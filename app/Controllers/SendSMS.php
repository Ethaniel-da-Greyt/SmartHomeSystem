<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class SendSMS extends BaseController
{
    private $rateLimitWindow = 60; // 60 seconds (1 minute)
    
    public function index()
    {
        // Get the JSON input from ESP12
        $jsonInput = $this->request->getJSON(true);
        
        // Check if JSON was received
        if (!$jsonInput) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No JSON data received'
            ])->setStatusCode(400);
        }
        
        // Validate required fields
        if (empty($jsonInput['device_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Device ID is required'
            ])->setStatusCode(400);
        }
        
        if (empty($jsonInput['message'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Message is required'
            ])->setStatusCode(400);
        }
        
        // Get phone number from JSON (ESP12 already stores it)
        $phoneNumber = $jsonInput['phone_number']; //?? '+639108797192';
        
        if (empty($phoneNumber)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Phone number is required'
            ])->setStatusCode(400);
        }
        
        // Validate phone number format
        if (!$this->validatePhoneNumber($phoneNumber)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid phone number format. Must be +639XXXXXXXXX'
            ])->setStatusCode(400);
        }
        
        // Check rate limiting - combined device and phone
        $rateLimitCheck = $this->checkRateLimit($jsonInput['device_id'], $phoneNumber);
        
        if (!$rateLimitCheck['allowed']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Rate limit exceeded',
                'details' => 'Please wait before sending another SMS',
                'wait_time' => $rateLimitCheck['wait_time'],
                'next_allowed_time' => date('Y-m-d H:i:s', $rateLimitCheck['next_allowed_time'])
            ])->setStatusCode(429); // 429 Too Many Requests
        }
        
        // Prepare message with additional info
        $fullMessage = $this->prepareMessage($jsonInput);
        
        // Send SMS directly
        $result = sendTextBeeSMS($phoneNumber, $fullMessage);
        
        // Parse the response
        $responseData = json_decode($result, true);
        
        // Return response to ESP12
        if (isset($responseData['status']) && $responseData['status'] === 'success') {
            // Record successful SMS send for rate limiting
            $this->recordSMSSent($jsonInput['device_id'], $phoneNumber);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'SMS sent successfully',
                'data' => [
                    'device_id' => $jsonInput['device_id'],
                    'phone_number' => $phoneNumber,
                    'alert_type' => $jsonInput['alert_type'] ?? 'unknown',
                    'timestamp' => time(),
                    'rate_limit_info' => [
                        'limit' => '1 SMS per minute',
                        'next_allowed_time' => date('Y-m-d H:i:s', time() + $this->rateLimitWindow)
                    ]
                ]
            ])->setStatusCode(200);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to send SMS',
                'error' => $responseData['message'] ?? 'Unknown error from SMS gateway',
                'data' => $responseData
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Check rate limit for device-phone combination
     */
    private function checkRateLimit($deviceId, $phoneNumber)
    {
        $cacheKey = "sms_rate_limit_{$deviceId}_{$phoneNumber}";
        
        // Get cache service
        $cache = \Config\Services::cache();
        
        // Try to get last sent timestamp from cache
        $lastSent = $cache->get($cacheKey);
        
        if ($lastSent === null) {
            // No previous SMS sent from this device-phone combination
            return [
                'allowed' => true,
                'wait_time' => 0,
                'next_allowed_time' => time()
            ];
        }
        
        $currentTime = time();
        $timeElapsed = $currentTime - $lastSent;
        
        if ($timeElapsed < $this->rateLimitWindow) {
            // Rate limit exceeded
            $waitTime = $this->rateLimitWindow - $timeElapsed;
            return [
                'allowed' => false,
                'wait_time' => $waitTime,
                'next_allowed_time' => $lastSent + $this->rateLimitWindow
            ];
        }
        
        // Rate limit not exceeded
        return [
            'allowed' => true,
            'wait_time' => 0,
            'next_allowed_time' => $currentTime
        ];
    }
    
    /**
     * Record SMS sent for rate limiting
     */
    private function recordSMSSent($deviceId, $phoneNumber)
    {
        $cacheKey = "sms_rate_limit_{$deviceId}_{$phoneNumber}";
        
        // Get cache service
        $cache = \Config\Services::cache();
        
        // Store current timestamp
        // Cache for 2x rate limit window to prevent rapid retries
        $cache->save($cacheKey, time(), $this->rateLimitWindow * 2);
        
        // Also store in recent activity cache (last 10 messages for monitoring)
        $this->storeRecentActivity($deviceId, $phoneNumber);
    }
    
    /**
     * Store recent activity for monitoring (cache-based, no database needed)
     */
    private function storeRecentActivity($deviceId, $phoneNumber)
    {
        $activityKey = "sms_recent_activity_{$deviceId}";
        $cache = \Config\Services::cache();
        
        // Get existing activity or create new array
        $activity = $cache->get($activityKey);
        if (!$activity || !is_array($activity)) {
            $activity = [];
        }
        
        // Add new activity entry
        $activity[] = [
            'timestamp' => time(),
            'phone_number' => $phoneNumber,
            'date_time' => date('Y-m-d H:i:s')
        ];
        
        // Keep only last 10 entries
        if (count($activity) > 10) {
            $activity = array_slice($activity, -10);
        }
        
        // Store for 1 hour
        $cache->save($activityKey, $activity, 3600);
    }
    
    /**
     * Validate phone number format
     * Accepts Philippine numbers: +639XXXXXXXXX
     */
    private function validatePhoneNumber($phoneNumber)
    {
        // Check if it's a valid Philippine mobile number
        // Format: +639XXXXXXXXX
        if (strlen($phoneNumber) !== 13) {
            return false;
        }
        
        if (!preg_match('/^\+639[0-9]{9}$/', $phoneNumber)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Prepare the full message with additional information
     */
    private function prepareMessage($jsonInput)
    {
        $message = $jsonInput['message'];
        
        // Add device ID if not already in message
        if (!strpos($message, $jsonInput['device_id'])) {
            $message .= "\nDevice: " . $jsonInput['device_id'];
        }
        
        // Add timestamp if not already in message
        $timestamp = date('Y-m-d H:i:s');
        if (!strpos($message, $timestamp)) {
            $message .= "\nTime: " . $timestamp;
        }
        
        // Add alert type if available
        if (!empty($jsonInput['alert_type'])) {
            $message .= "\nType: " . ucfirst(str_replace('_', ' ', $jsonInput['alert_type']));
        }
        
        // Limit message length for SMS (usually 160 characters)
        if (strlen($message) > 155) {
            $message = substr($message, 0, 152) . '...';
        }
        
        return $message;
    }
    
    /**
     * Test endpoint for manual SMS sending (optional)
     */
    public function test()
    {
        // Check rate limit for test
        $phoneNumber = $this->request->getGet('phone'); //?? '+639108797192';
        $deviceId = $this->request->getGet('device_id') ?? 'test_device';
        $message = $this->request->getGet('message') ?? 'Test SMS from CI4 + TextBee API';
        
        // Validate phone number
        if (!$this->validatePhoneNumber($phoneNumber)) {
            return "Invalid phone number format: $phoneNumber. Must be +639XXXXXXXXX";
        }
        
        // Check rate limit
        $rateLimitCheck = $this->checkRateLimit($deviceId, $phoneNumber);
        
        if (!$rateLimitCheck['allowed']) {
            $waitTime = $rateLimitCheck['wait_time'];
            return "Rate limit exceeded. Please wait {$waitTime} seconds before trying again.";
        }
        
        $result = sendTextBeeSMS($phoneNumber, $message);
        $responseData = json_decode($result, true);
        
        // Record successful SMS
        if (isset($responseData['status']) && $responseData['status'] === 'success') {
            $this->recordSMSSent($deviceId, $phoneNumber);
        }
        
        echo "<pre>";
        echo "Device ID: $deviceId\n";
        echo "Phone Number: $phoneNumber\n";
        echo "Message: $message\n";
        echo "Rate Limit: " . ($rateLimitCheck['allowed'] ? 'Allowed' : 'Not Allowed') . "\n";
        echo "SMS API Response:\n";
        print_r($responseData);
        echo "</pre>";
    }
    
    /**
     * Health check endpoint (optional)
     */
    public function health()
    {
        return $this->response->setJSON([
            'status' => 'healthy',
            'service' => 'SMS Gateway',
            'rate_limit' => '1 SMS per minute per device-phone combination',
            'cache_driver' => env('cache.defaultHandler', 'file'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get rate limit status (optional)
     */
    public function getRateLimitStatus()
    {
        $deviceId = $this->request->getGet('device_id');
        $phoneNumber = $this->request->getGet('phone_number');
        
        if (!$deviceId || !$phoneNumber) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Both device_id and phone_number are required'
            ])->setStatusCode(400);
        }
        
        $rateLimitCheck = $this->checkRateLimit($deviceId, $phoneNumber);
        
        return $this->response->setJSON([
            'status' => 'success',
            'device_id' => $deviceId,
            'phone_number' => $phoneNumber,
            'rate_limit_info' => [
                'allowed' => $rateLimitCheck['allowed'],
                'wait_time' => $rateLimitCheck['wait_time'],
                'next_allowed_time' => date('Y-m-d H:i:s', $rateLimitCheck['next_allowed_time']),
                'limit' => '1 SMS per minute'
            ],
            'recent_activity' => $this->getRecentActivity($deviceId)
        ]);
    }
    
    /**
     * Get recent activity for a device (cache-based)
     */
    private function getRecentActivity($deviceId)
    {
        $activityKey = "sms_recent_activity_{$deviceId}";
        $cache = \Config\Services::cache();
        
        $activity = $cache->get($activityKey);
        
        return $activity ?: [];
    }
    
    /**
     * Clear rate limit for specific device-phone (optional, for admin/testing)
     */
    public function clearRateLimit()
    {
        $deviceId = $this->request->getGet('device_id');
        $phoneNumber = $this->request->getGet('phone_number');
        
        if (!$deviceId || !$phoneNumber) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Both device_id and phone_number are required'
            ])->setStatusCode(400);
        }
        
        $cacheKey = "sms_rate_limit_{$deviceId}_{$phoneNumber}";
        $cache = \Config\Services::cache();
        
        if ($cache->delete($cacheKey)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Rate limit cleared successfully',
                'device_id' => $deviceId,
                'phone_number' => $phoneNumber
            ]);
        }
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'No rate limit found or already cleared'
        ]);
    }
    
    /**
     * Get all rate limited devices (for monitoring)
     * Note: This works best with Redis or Memcached. For file cache, it's limited.
     */
    public function getAllRateLimits()
    {
        // This method works best with Redis/Memcached
        // For file cache, you'd need to manually scan cache directory
        
        $cache = \Config\Services::cache();
        
        // Note: CI4's cache doesn't have a "get all keys" method by default
        // This is a simplified version that requires manual cache driver setup
        
        return $this->response->setJSON([
            'status' => 'info',
            'message' => 'For detailed monitoring, consider using Redis cache driver',
            'rate_limit_window' => $this->rateLimitWindow,
            'cache_driver' => env('cache.defaultHandler', 'file')
        ]);
    }
}
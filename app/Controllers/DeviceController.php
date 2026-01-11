<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RemoteModel;

class DeviceController extends ResourceController
{
    protected $format = 'json';

    // GET current device state (for ESP12E to check)
    public function api_device_state($deviceId)
    {
        $deviceModel = new RemoteModel();
        $device = $deviceModel->where('device_id', $deviceId)->first();

        if (!$device) {
            return $this->failNotFound('Device not found');
        }

        return $this->respond([
            'device_id' => $device['device_id'],
            'state' => strtoupper($device['state'] ?? 'OFF')
        ]);
    }

    // PUT: Update device state from ESP12E (when door closes)
    public function api_device_state_update($deviceId)
    {
        $deviceModel = new RemoteModel();

        $newState = strtoupper($this->request->getJSON(true)['state'] ?? '');

        if (!in_array($newState, ['ON', 'OFF'])) {
            return $this->failValidationErrors('State must be ON or OFF');
        }

        $device = $deviceModel->where('device_id', $deviceId)->first();
        if (!$device) {
            return $this->failNotFound('Device not found');
        }

        $deviceModel->update($device['id'], ['state' => $newState]);

        return $this->respond([
            'status' => 'success',
            'device_id' => $deviceId,
            'state' => $newState
        ]);
    }

    // POST toggle device (Web UI → Database)
    public function api_device_toggle()
    {
        $deviceModel = new RemoteModel();

        $deviceId = $this->request->getJSON(true)['device_id'] ?? null;
        $newState = strtoupper($this->request->getJSON(true)['state'] ?? '');

        if (!$deviceId || !$newState) {
            return $this->failValidationErrors('Missing device_id or state');
        }

        $device = $deviceModel->where('device_id', $deviceId)->first();
        if (!$device) {
            return $this->failNotFound('Device not found');
        }

        $deviceModel->update($device['id'], ['state' => $newState]);

        return $this->respond([
            'status' => 'success',
            'device_id' => $deviceId,
            'state' => $newState
        ]);
    }
}
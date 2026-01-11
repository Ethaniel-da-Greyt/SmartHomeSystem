<?php

namespace App\Controllers;

use App\Models\RemoteModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\KilowattsReaderModel;

class Smarthome extends ResourceController
{
    protected $modelName = KilowattsReaderModel::class;
    protected $format = 'json';

    // API endpoint: POST /smarthome/api/kwh
    public function api_kwh()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['device_id']) || !isset($data['kwh'])) {
            return $this->failValidationErrors('Missing device_id or kwh');
        }

        $deviceId = $data['device_id'];
        $kwh = $data['kwh'];

        if($kwh == "0.0000" || "0"){
            return false;
        }

        $deviceModel = new RemoteModel();
        $kwhModel = new KilowattsReaderModel();

        // ================= AUTO-REGISTER / HEARTBEAT =================
        $device = $deviceModel
            ->where('device_id', $deviceId)
            ->first();

        if (!$device) {
            // First time seen → register
            $deviceModel->insert([
                'device_id' => $deviceId,
                'state' => 'OFF',
                'is_online' => 1,
                'last_seen' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Existing device → heartbeat
            $deviceModel
                ->where('device_id', $deviceId)
                ->set([
                    'is_online' => 1,
                    'last_seen' => date('Y-m-d H:i:s')
                ])
                ->update();
        }

        // ================= SAVE kWh =================
        $kwhModel->insert([
            'esp_id' => $deviceId,
            'kilowatts' => $kwh
        ]);

        return $this->respond([
            'status' => 'success',
            'device_id' => $deviceId,
            'kwh' => $kwh
        ], 201);
    }
}

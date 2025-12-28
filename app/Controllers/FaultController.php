<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FaultModel;

class FaultController extends ResourceController
{
    protected $format = 'json';

    public function api_store()
    {
        $data = $this->request->getJSON();

        if (!$data || !isset($data->device_id) || !isset($data->faults)) {
            return $this->failValidationErrors('Missing device_id or faults');
        }

        $faultModel = new FaultModel();

        // Split multiple faults by line break
        $faultLines = explode("\n", $data->faults);
        $savedCount = 0;

        foreach ($faultLines as $fault) {
            $fault = trim($fault);
            if (!empty($fault)) {
                $faultModel->insert([
                    'device_id' => $data->device_id,
                    'fault_message' => $fault
                ]);
                $savedCount++;
            }
        }

        return $this->respond([
            'status' => 'success',
            'saved' => $savedCount
        ]);
    }
}

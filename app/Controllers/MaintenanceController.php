<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DeviceModel;
use App\Models\FaultModel;

class MaintenanceController extends BaseController
{
    public function maintenance()
    {
        $deviceModel = new FaultModel();

        // Fetch all devices
        $data = $deviceModel->findAll();

        return view('pages/maintenance', ['faults' => $data]);
    }
}

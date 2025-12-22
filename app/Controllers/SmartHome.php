<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SmartHome extends BaseController
{
    private $espIp = 'http://10.201.136.44'; // ESP8266 IP

    public function index()
    {
        // Only fetch status without changing relay
        $statusJson = file_get_contents($this->espIp . '/power'); 
        $data['status'] = json_decode($statusJson, true);

        return view('smarthome', $data);
    }

    public function setPower($state)
    {
        $state = $state ? 1 : 0;
        file_get_contents($this->espIp . '/power?state=' . $state);
        return redirect()->to('/smarthome');
    }
}

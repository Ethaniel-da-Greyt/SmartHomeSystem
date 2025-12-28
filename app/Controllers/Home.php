<?php

namespace App\Controllers;

use App\Models\KilowattsReaderModel;
use App\Models\RemoteModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }


    public function dashboard()
    {
        try {
            $model = new KilowattsReaderModel();

            $currentMonth = date('m');
            $currentYear = date('Y');

            // Fetch all kWh readings for the current month, grouped by day
            $builder = $model->builder();
            $query = $builder
                ->select('DATE(date_created) as date, SUM(kilowatts) as total_kwh')
                ->where('MONTH(date_created)', $currentMonth)
                ->where('YEAR(date_created)', $currentYear)
                ->groupBy('DATE(date_created)')
                ->orderBy('DATE(date_created)', 'ASC')
                ->get();

            $results = $query->getResultArray();

            // Initialize array with 0 for each day of the month
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            $monthlyKwh = [];
            $labels = [];

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dateStr = date('Y-m-d', strtotime("$currentYear-$currentMonth-$i"));
                $labels[] = date('M d, Y', strtotime($dateStr)); // e.g., "Dec 27, 2025"
                $monthlyKwh[$dateStr] = 0;
            }

            // Fill actual data
            foreach ($results as $row) {
                $date = $row['date'];
                if (isset($monthlyKwh[$date])) {
                    $monthlyKwh[$date] = floatval($row['total_kwh']);
                }
            }

            // Summary metrics
            $totalKwh = array_sum($monthlyKwh);
            $avgDaily = $daysInMonth ? ($totalKwh / $daysInMonth) : 0;
            $ratePerKwh = 12; // Example rate
            $estimatedCost = $totalKwh * $ratePerKwh;

            // Prepare data array for view
            $data = [
                'monthlyKwh' => array_values($monthlyKwh), // just values for chart.js
                'labels' => $labels,
                'totalKwh' => $totalKwh,
                'avgDaily' => $avgDaily,
                'estimatedCost' => $estimatedCost
            ];

            return view('pages/dashboard', $data);

        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return view('pages/dashboard', [
                'monthlyKwh' => [],
                'labels' => [],
                'totalKwh' => 0,
                'avgDaily' => 0,
                'estimatedCost' => 0
            ]);
        }
    }

    public function remote_control()
    {
        $model = new RemoteModel();

        $getDevice = $model->first();

        if(!$getDevice)
        {
            return redirect()->back()->withInput()->with('error', 'No device Found.');
        }

        
        return view('pages/remote-control', ['esp_id' => $getDevice['device_id']]);
    }


}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class WattVisionController extends Controller
{
    /**
     * Display the WattVision dashboard.
     */
    public function index()
    {
        // 1. Generate live metrics (W, kWh, Bs.)
        $currentWatts = rand(220, 250) + (rand(0, 10) / 10); // Normal live usage in Watts
        
        // Let's check if there's a spike or vampire load simulation
        $hour = (int) now()->format('H');
        if ($hour >= 18 && $hour <= 22) {
            // Peak hours: higher usage
            $currentWatts += rand(1500, 2200);
        } elseif ($hour >= 1 && $hour <= 5) {
            // Night time: vampire draw
            $currentWatts = rand(80, 120) + (rand(0, 10) / 10);
        }

        $kwhToday = 14.25;
        $costTodayBs = $kwhToday * 0.85; // Average tariff in Bolivia (Bs. per kWh)

        // 2. Generate 24 hours consumption history for the chart
        $history = [];
        for ($i = 23; $i >= 0; $i--) {
            $time = now()->subHours($i);
            $h = (int) $time->format('H');
            
            // Base usage by time of day
            if ($h >= 18 && $h <= 22) {
                $watts = rand(1800, 2600); // peak (lights, cooking, electronics)
            } elseif ($h >= 1 && $h <= 5) {
                $watts = rand(70, 130); // vampire load
            } else {
                $watts = rand(300, 800); // normal daytime
            }
            
            $history[] = [
                'label' => $time->format('H:i'),
                'watts' => $watts,
                'kwh' => round($watts / 1000, 3)
            ];
        }

        // 3. Current active alerts (vampire load, consumption spikes)
        $alerts = [];
        if ($currentWatts > 1500) {
            $alerts[] = [
                'id' => 1,
                'title' => 'Pico de Consumo Detectado',
                'description' => 'El consumo supera los 1.5 kW. Verifique calefactores o electrodomésticos de alto consumo.',
                'time' => 'Hace un momento',
                'severity' => 'danger'
            ];
        }
        
        // Vampire load warning at night
        if ($hour >= 1 && $hour <= 5 && $currentWatts > 100) {
            $alerts[] = [
                'id' => 2,
                'title' => 'Consumo Vampiro Elevado',
                'description' => 'Consumo continuo detectado durante la madrugada. Apague dispositivos en standby.',
                'time' => 'Hace 1 hora',
                'severity' => 'warning'
            ];
        }

        // Default alerts if empty
        if (empty($alerts)) {
            $alerts[] = [
                'id' => 3,
                'title' => 'Funcionamiento Normal',
                'description' => 'Todos los sistemas eléctricos operan dentro de los rangos de consumo óptimos.',
                'time' => 'Hace 10 min',
                'severity' => 'success'
            ];
        }

        return Inertia::render('WattVision/Dashboard', [
            'metrics' => [
                'current_watts' => $currentWatts,
                'kwh_today' => $kwhToday,
                'cost_today_bs' => round($costTodayBs, 2),
                'tariff_rate' => 0.85
            ],
            'history' => $history,
            'alerts' => $alerts,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\GymSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = GymSetting::all()->pluck('value', 'key')->toArray();

        // Database & System Diagnostics
        $dbConnection = config('database.default');
        $dbVersion = 'Unknown';
        try {
            $pdo = DB::connection()->getPdo();
            $dbVersion = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable $e) {
            $dbVersion = 'Connection pending ('.$e->getMessage().')';
        }

        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'db_connection' => $dbConnection,
            'db_server_version' => $dbVersion,
            'offline_mode' => config('gym.offline_network_mode', true) ? 'Active (Internal Network Ready)' : 'Standard',
            'storage_path_writable' => is_writable(storage_path()),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
        ];

        return view('settings.index', compact('settings', 'systemInfo'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'gym_name' => 'required|string|max:150',
            'gym_tagline' => 'nullable|string|max:200',
            'gym_phone' => 'nullable|string|max:50',
            'gym_email' => 'nullable|email|max:150',
            'gym_address' => 'nullable|string|max:255',
            'tax_rate_percentage' => 'required|numeric|min:0|max:50',
            'currency_symbol' => 'required|string|max:5',
            'grace_period_days' => 'required|integer|min:0|max:30',
        ]);

        foreach ($validated as $key => $val) {
            GymSetting::set($key, $val, 'general');
        }

        return back()->with('success', 'Gym system settings updated successfully!');
    }
}

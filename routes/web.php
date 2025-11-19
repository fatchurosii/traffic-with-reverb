<?php

use App\Events\TrafficConfig;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/traffic', function () {
    return view('traffic');
});

Route::get('/traffic-data', function () {
    $message = [
        "data" => [
            "id" => Str::uuid7(),
            "location_name" => "pandegiling barat",
            "schedule" => [
                "id" => Str::uuid7(),
                "start_date" => now()->format("Y-m-d H:i:s"),
                "end_date" => now()->subDays(2)->format("Y-m-d H:i:s"),
                "rules" => [
                    [
                        "id" => Str::uuid7(),
                        "start_time" => '07:00:00',
                        "end_time" =>  '10:00:00',
                        "red_light" => 60000,
                        "yellow_light" => 10000,
                        "green_light" => 60000
                    ],
                    [
                        "id" => Str::uuid7(),
                        "start_time" => '07:00:00',
                        "end_time" =>  '10:00:00',
                        "red_light" => 120000,
                        "yellow_light" => 5000,
                        "green_light" => 60000
                    ]
                ]
            ]
        ]
    ];
    broadcast(new TrafficConfig($message));
});

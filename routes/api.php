<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {

    // Employee routes
    Route::get('/employees', [EmployeeApiController::class, 'index']);
    Route::get('/employees/{id}', [EmployeeApiController::class, 'show']);
    Route::post('/employees', [EmployeeApiController::class, 'store']);
    Route::put('/employees/{id}', [EmployeeApiController::class, 'update']);
    Route::patch('/employees/{id}', [EmployeeApiController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeeApiController::class, 'destroy']);

    // Example external API call (weather)
    Route::get('/weather', function () {
        $url = "https://api.open-meteo.com/v1/forecast?latitude=12.67&longitude=123.87&current_weather=true";
        $json = @file_get_contents($url);

        if ($json === false) {
            return response()->json([
                "error" => "Failed to fetch weather data."
            ], 500);
        }

        return response()->json(json_decode($json), 200);
    });

    // Test / status route
    Route::get('/status', function () {
        return response()->json([
            "project" => "Bulan Payroll API",
            "version" => "1.0",
            "status" => "API is working"
        ]);
    });

});

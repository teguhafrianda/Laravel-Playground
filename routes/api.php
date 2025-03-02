<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

const SUCCESS_UNIT_TESTING = 80;

$response = function ($value): array {
    return array(
        'name' => $value['name'],
        'stack' => $value['stack'],
        'percentage' => array_sum($value['unit_testing_skor']) . '%',
        'status' => array_sum($value['unit_testing_skor']) >= SUCCESS_UNIT_TESTING ? 'success' : 'failed'
    );
};

Route::post('/project/create/cortex', function (Request $request) use ($response) {

    /**
     * create request BODY
     */
    $unit_testing = [];
    $request = $request->post();
    foreach ($request as $key => $value) {
        $value = $response($value);
        array_push($unit_testing, $value);
    }

    return response()->json($unit_testing);
});

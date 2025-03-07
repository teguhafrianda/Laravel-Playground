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
Route::post('/try', function (Request $request) {

    $project = $request->input('project');
    $pajak = $request->input('pajak');
    $korupsi = $request->input('korupsi');

    return hitungKorupsi($project, $pajak, $korupsi);
});


function hitungKorupsi($project, $pajak, $korupsi) {

    if ($project <= 0 || $pajak < 0 || $korupsi < 0) {
        return response()->json(['message' => 'Masukkan data yang valid']);
    }

    $project_setelah_pajak = $project - $pajak;


    if ($project_setelah_pajak <= 0) {
        return response()->json(['message' => 'Pajak lebih besar atau sama dengan nilai proyek. Perhitungan tidak valid']);
    }

    $persentase_korupsi = ($korupsi / $project_setelah_pajak) * 100;

    return response()->json([
        'project_setelah_pajak' => $project_setelah_pajak,
        'persentase_korupsi' => round($persentase_korupsi, 2) . '%'
    ]);
}
?>

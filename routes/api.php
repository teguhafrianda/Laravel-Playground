<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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

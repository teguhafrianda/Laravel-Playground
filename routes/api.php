<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

function hitungPPN($harga)
{
    if (!is_numeric($harga) || $harga < 0) {
        return "Harga tidak valid. Harus berupa angka positif.";
    }
    
    $pajak = fn($value) => $value / 100;
    $ppn = $harga * $pajak(11);
    $harga_setelah_pajak = $harga - $ppn;
    
    return [
        'harga_awal' => $harga,
        'ppn' => $ppn,
        'harga_setelah_pajak' => $harga_setelah_pajak
    ];
}

Route::post('/hitung/korupsi', function (Request $request) {
    $proyek = (int) $request->input('proyek');
    $persentase_korupsi = (float) str_replace('%', '', $request->input('korupsi'));

    if (!is_numeric($proyek) || $proyek < 0) {
        return response()->json(["error" => "Harga tidak valid. Harus berupa angka positif."], 400);
    }

    if ($persentase_korupsi < 0 || $persentase_korupsi > 100) {
        return response()->json(["error" => "Persentase korupsi tidak valid. Harus antara 0-100."], 400);
    }

    // Hitung nilai setelah pajak
    $data_pajak = hitungPPN($proyek);
    $nilai_setelah_pajak = $data_pajak['harga_setelah_pajak'];

    // Hitung korupsi dari nilai setelah pajak
    $nilai_korupsi = $nilai_setelah_pajak * ($persentase_korupsi / 100);
    $nilai_proyek_akhir = $nilai_setelah_pajak - $nilai_korupsi;

    return response()->json([
        "nilai_proyek_awal" => number_format($proyek),
        "ppn" => number_format($data_pajak['ppn']),
        "nilai_setelah_pajak" => number_format($nilai_setelah_pajak),
        "persentase_korupsi" => $persentase_korupsi . "%",
        "nilai_korupsi" => number_format($nilai_korupsi),
        "nilai_proyek_akhir" => number_format($nilai_proyek_akhir)
    ]);
});
?>

<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

function hitungPPN($harga, $pajak_persen)
{
    if (!is_int($harga) || $harga < 0) {
        return "Harga tidak valid. Harus berupa bilangan bulat positif.";
    }
    
    $pajak = fn($value) => $value / 100;
    $ppn = (int) ($harga * $pajak($pajak_persen));
    $harga_setelah_pajak = $harga - $ppn;
    
    return [
        'harga_awal' => $harga,
        'ppn' => $ppn,
        'harga_setelah_pajak' => $harga_setelah_pajak
    ];
}

Route::post('/hitung/korupsi', function (Request $request) {
    $pajak = filter_var($request->input('pajak'), FILTER_VALIDATE_INT);
    $nilai_proyek = filter_var($request->input('nilai_proyek'), FILTER_VALIDATE_INT);
    $nilai_korupsi = filter_var($request->input('nilai_korupsi'), FILTER_VALIDATE_INT);

    if ($nilai_proyek === false || $nilai_proyek < 0) {
        return response()->json(["error" => "Nilai proyek tidak valid. Harus berupa bilangan bulat positif."], 400);
    }

    if ($pajak === false || $pajak < 0 || $pajak > 100) {
        return response()->json(["error" => "Persentase pajak tidak valid. Harus berupa bilangan bulat antara 0-100."], 400);
    }

    if ($nilai_korupsi === false || $nilai_korupsi < 0 || $nilai_korupsi > $nilai_proyek) {
        return response()->json(["error" => "Nilai korupsi tidak valid. Harus berupa bilangan bulat positif dan tidak lebih dari nilai proyek."], 400);
    }

    // Hitung nilai setelah pajak
    $data_pajak = hitungPPN($nilai_proyek, $pajak);
    $nilai_setelah_pajak = $data_pajak['harga_setelah_pajak'];

    if ($nilai_korupsi > 0) {
        // Pastikan nilai korupsi tidak lebih besar dari nilai setelah pajak
        $nilai_korupsi = min($nilai_korupsi, $nilai_setelah_pajak);
        $nilai_proyek_akhir = $nilai_setelah_pajak - $nilai_korupsi;
        $persentase_korupsi = ($nilai_korupsi / $nilai_setelah_pajak) * 100;

        return response()->json([
            "Pesan" => "Project terindikasi korupsi dan bayar pajak serta bayar korupsi",
            "status" => "success",
            "project_nominal" => "Rp " . number_format($nilai_proyek),
            "pajak" => "Rp " . number_format($data_pajak['ppn']),
            "korupsi" => number_format($persentase_korupsi, 2) . "%",
            "korupsi_nominal" => "Rp " . number_format($nilai_korupsi),
            "proyek_sebenarnya" => "Rp " . number_format($nilai_proyek_akhir)
        ]);
    } else {
        return response()->json([
            "Pesan" => "Project tidak terindikasi korupsi, hanya membayar pajak",
            "status" => "success",
            "project_nominal" => "Rp " . number_format($nilai_proyek,),
            "pajak" => "Rp " . number_format($data_pajak['ppn']),
            "proyek_sebenarnya" => "Rp " . number_format($nilai_setelah_pajak)
        ]);
    }
});

Route::post('/evaluasi/tim', function (Request $request) {
    $team = $request->input('team');
    
    if (!is_array($team) || count($team) === 0) {
        return response()->json(["error" => "Data tim tidak valid."]);
    }
    
    $total_dev = count($team);
    $failed_dev = array_filter($team, fn($dev) => $dev['status'] === 'failed');
    $failed_count = count($failed_dev);
    
    $failure_percentage = ($failed_count / $total_dev) * 100;
    
    if ($failed_count === 80) {
        return response()->json([
            "message" => "Aplikasi berhasil memenuhi ekspektasi",
            "status" => "success",
            "testing_percentage" => (100 - $failure_percentage) . "%"
        ]);
    }
    
    return response()->json([
        "message" => "Aplikasi gagal memenuhi ekspektasi",
        "status" => "failed",
        "failure_percentage" => number_format($failure_percentage, 2) . "%",
        "problem" => "Terdapat kesalahan dalam beberapa modul aplikasi",
        "team_issues" => array_values($failed_dev),
        "suggestion" => "Tingkatkan komunikasi tim dan lakukan code review lebih ketat."
    ]);
});
?>

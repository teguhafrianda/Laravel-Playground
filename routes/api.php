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

Route::post('/compare', function (Request $request) {
    $participants = $request->all();

    if (!is_array($participants) || empty($participants)) {
        return response()->json(["error" => "Input tidak valid. Harus berupa array peserta dengan unit testing."], 400);
    }

    $calculateScore = function ($participant) {
        if (!isset($participant['name']) || !isset($participant['stack']) || !is_array($participant['unit_testing_skor'])) {
            return ["name" => $participant['name'] ?? "Unknown", "error" => "Data unit testing tidak valid atau tidak lengkap."];
        }

        $totalPoints = array_sum($participant['unit_testing_skor']);
        $maxPoints = count($participant['unit_testing_skor']) * 20;
        $percentage = $maxPoints > 0 ? min(($totalPoints / $maxPoints) * 100, 100) : 0;
        $status = $totalPoints >= 80 ? "Sukses" : "Gagal";

        return [
            "name" => $participant['name'],
            "stack" => $participant['stack'],
            "totalPoints" => $totalPoints,
            "percentage" => number_format($percentage, 2) . "%",
            "status" => $status
        ];
    };

    $scores = array_map($calculateScore, $participants);
    usort($scores, fn($a, $b) => floatval($b['percentage']) <=> floatval($a['percentage']));
    
    $bestParticipant = $scores[0];
    $worstParticipant = end($scores);

    return response()->json([
        "message" => isset($bestParticipant['error']) ? "Tidak dapat menentukan peserta terbaik karena data tidak valid." : "Peserta terbaik berdasarkan hasil unit testing.",
        "bestParticipant" => $bestParticipant,
        "worstParticipant" => $worstParticipant
    ]);
});

Route::post('/project/lomba/cortex', function (Request $request) {
    $teams = $request->all();

    if (!is_array($teams) || empty($teams)) {
        return response()->json(["error" => "Input tidak valid. Harus berupa array tim dengan unit testing."], 400);
    }

    $calculateScore = function ($team) {
        if (!isset($team['name']) || !isset($team['stack']) || !is_array($team['unit_testing_skor'])) {
            return ["name" => $team['name'] ?? "Unknown", "error" => "Data unit testing tidak valid atau tidak lengkap."];
        }

        $totalPoints = array_sum($team['unit_testing_skor']);
        $maxPoints = count($team['unit_testing_skor']) * 20;
        $percentage = $maxPoints > 0 ? min(($totalPoints / $maxPoints) * 100, 100) : 0;
        $status = $totalPoints >= 80 ? "Sukses" : "Gagal";

        return [
            "name" => $team['name'],
            "stack" => $team['stack'],
            "totalPoints" => $totalPoints,
            "percentage" => number_format($percentage, 2) . "%",
            "status" => $status
        ];
    };

    $teamScores = array_map($calculateScore, $teams);
    usort($teamScores, fn($a, $b) => floatval($b['percentage']) <=> floatval($a['percentage']));
    
    $bestTeam = $teamScores[0];
    $worstTeam = end($teamScores);

    return response()->json([
        "message" => isset($bestTeam['error']) ? "Tidak dapat menentukan tim terbaik karena data tidak valid." : "Tim terbaik berdasarkan hasil unit testing.",
        "bestTeam" => $bestTeam,
        "worstTeam" => $worstTeam
    ]);
});
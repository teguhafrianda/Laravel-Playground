<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//functional programming

//set struct data handler
//const project nominal
const project_nominal = 30000000;

//tax input
$HandlerTaxInput = fn($value): float => $value / 100;

//handler set nominal project apakah melebihi dari project nominal
$HandlerValidateNominalProject = fn($value): bool => $value >= project_nominal ? true : false;

//handler set bayar pajak
$HandlerPayTax = fn(int $tax_amount, int $project_value): int => $project_value - $tax_amount;

//handler set menentukan pajak yang harus dibayar
$HandlerDecitionPayTax = fn(int $project_value, float $tax_value): int => $project_value * $tax_value;

//handler set persentase korupsi
$HandlerCorruptionPercentage = fn(int $corruption_amount, int $project_value): float => ($corruption_amount / $project_value) * 100;

//handler set sisa saldo setelah korupsi
$HandlerBalanceAfterCorruption = fn(int $project_value, int $corruption_amount): int => $project_value - $corruption_amount;

//handler format rupiah
$HandlerFormatRupiah = fn(int $value): string => "Rp " . number_format($value, 0, ',', '.');

//handler format percentage
$HandlerFormatPercentage = fn(float $value): string => number_format($value, 2, '.', '') . '%';


Route::post('/count/corruption/coretax', function (Request $request) use (
    $HandlerTaxInput,
    $HandlerValidateNominalProject,
    $HandlerPayTax,
    $HandlerDecitionPayTax,
    $HandlerCorruptionPercentage,
    $HandlerBalanceAfterCorruption,
    $HandlerFormatRupiah,
    $HandlerFormatPercentage
) {
    //req: body parser
    $tax = $request->post('tax');
    $project_value = $request->post('project_value');
    $corruption = $request->post('corruption');

    //validate input dan memastikan input tidak kosong
    if (empty($tax) || empty($project_value) || empty($corruption)) {
        return response()->json([
            'message' => 'Input gak boleh kosong',
            'hint' => 'Please insert all input: tax, project_value, corruption',
            'status' => 'error'
        ], 422);
    }

    //validate tipe input hanya berupa integer
    if (!is_int($tax) || !is_int($project_value) || !is_int($corruption)) {
        return response()->json([
            'message' => 'Input harus berupa integer',
            'status' => 'error'
        ], 422);
    }

    //nominal project kurang dari project nominal
    if (!$HandlerValidateNominalProject($project_value)) {
        $tax_value = $HandlerTaxInput($tax);
        $tax_amount = $HandlerDecitionPayTax($project_value, $tax_value);
        return response()->json([
            'message' => 'Project tidak terindikasi korupsi dan hanya bayar pajak',
            'status' => 'success',
            'project_amount' => $HandlerFormatRupiah($project_value),
            'tax_amount' => $HandlerFormatRupiah($tax_amount),
            'balance' => $HandlerFormatRupiah($HandlerPayTax($tax_amount, $project_value))
        ], 200);
    }

    //nominal project lebih dari project nominal (indikasi korupsi)
    if ($HandlerValidateNominalProject($project_value)) {
        $tax_value = $HandlerTaxInput($tax);
        $tax_amount = $HandlerDecitionPayTax($project_value, $tax_value);
        $pay_tax = $HandlerPayTax($tax_amount, $project_value);
        return response()->json([
            'message' => 'Project terindikasi korupsi dan bayar pajak dan bayar korupsi',
            'status' => 'success',
            'project_amount' => $HandlerFormatRupiah($project_value),
            'tax_amount' => $HandlerFormatRupiah($tax_amount),
            'corruption_percentage' => $HandlerFormatPercentage($HandlerCorruptionPercentage($corruption, $pay_tax)),
            'corruption_nominal' => $HandlerFormatRupiah($corruption),
            'balance' => $HandlerFormatRupiah($HandlerBalanceAfterCorruption($pay_tax, $corruption))
        ], 200);
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

    function hitung($X) {
        return (10 - 4) / 1; // X = (10 - 4) / 1
    }
    
});

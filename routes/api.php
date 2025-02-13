<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//functional programming

//set struct data handler
//const project nominal
const project_nominal = 30000000;

//tax input
$HandlerTaxInput = fn($value): float => $value / 100;

//handler set nominal project apakah melebihi dari project nominal
$HandlerValidateNominalProject = fn($value): bool => $value >= project_nominal ? true : false;

//handler set bayar pajak
$HandlerPayTax = fn($tax_amount, $project_value): int => $project_value - $tax_amount;

//handler set menentukan pajak yang harus dibayar
$HandlerDecitionPayTax = fn($project_value, $tax_value): int => $project_value * $tax_value;

//handler set persentase korupsi
$HandlerCorruptionPercentage = fn($corruption_amount, $project_value): float => ($corruption_amount / $project_value) * 100;

//handler set sisa saldo setelah korupsi
$HandlerBalanceAfterCorruption = fn($project_value, $corruption_amount): int => $project_value - $corruption_amount;

Route::post('/count/corruption/coretax', function (Request $request) use (
    $HandlerTaxInput,
    $HandlerValidateNominalProject,
    $HandlerPayTax,
    $HandlerDecitionPayTax,
    $HandlerCorruptionPercentage,
    $HandlerBalanceAfterCorruption
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

    //validate input hanya berupa integer
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
            'message' => 'Project tidak indikasi korupsi dan hanya bayar pajak',
            'status' => 'success',
            'tax_amount' => $tax_amount,
            'balance' => $HandlerPayTax($tax_amount, $project_value)
        ], 200);
    }

    //nominal project lebih dari project nominal (indikasi korupsi)
    if ($HandlerValidateNominalProject($project_value)) {
        $tax_value = $HandlerTaxInput($tax);
        $tax_amount = $HandlerDecitionPayTax($project_value, $tax_value);
        $pay_tax = $HandlerPayTax($tax_amount, $project_value);
        return response()->json([
            'message' => 'Project indikasi korupsi dan bayar pajak dan korupsi',
            'status' => 'success',
            'tax_amount' => $tax_amount,
            'corruption_percentage' => $HandlerCorruptionPercentage($corruption, $pay_tax),
            'corruption_nominal' => $corruption,
            'balance' => $HandlerBalanceAfterCorruption($project_value, $corruption)
        ], 200);
    }
});

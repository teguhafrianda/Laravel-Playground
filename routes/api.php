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

//handler set project value if indicate corruption or not
$HandlerValidateNominalProject = fn($value): bool => $value >= project_nominal ? true : false;

//handler set pay tax
$HandlerPayTax = fn($tax_amount, $project_value): int => $project_value - $tax_amount;

//handler set decicion how to pay tax
$HandlerDecitionPayTax = fn($project_value, $tax_value): int => $project_value * $tax_value;

//handler set corruption amount
$HandlerCorruptionPercentage = fn($corruption_amount, $project_value): float => ($corruption_amount / $project_value) * 100;

//handler set balance after corruption
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

    //validate input is not empty make sure insert all input
    if (empty($tax) || empty($project_value) || empty($corruption)) {
        return response()->json([
            'message' => 'Input not empty',
            'hint' => 'Please insert all input: tax, project_value, corruption',
            'status' => 'error'
        ], 422);
    }

    //validate input only integer
    if (!is_int($tax) || !is_int($project_value) || !is_int($corruption)) {
        return response()->json([
            'message' => 'Input harus berupa angka',
            'status' => 'error'
        ], 422);
    }

    //nominal project less than project nominal
    if (!$HandlerValidateNominalProject($project_value)) {
        $tax_value = $HandlerTaxInput($tax);
        $tax_amount = $HandlerDecitionPayTax($project_value, $tax_value);
        return response()->json([
            'message' => 'Project not indicate corruption and only pay tax',
            'status' => 'success',
            'tax_amount' => $tax_amount,
            'balance' => $HandlerPayTax($tax_amount, $project_value)
        ], 200);
    }

    //nominal project greater than project nominal (indicate corruption)
    if ($HandlerValidateNominalProject($project_value)) {
        $tax_value = $HandlerTaxInput($tax);
        $tax_amount = $HandlerDecitionPayTax($project_value, $tax_value);
        $pay_tax = $HandlerPayTax($tax_amount, $project_value);
        return response()->json([
            'message' => 'Project indicate corruption and pay tax and corruption',
            'status' => 'success',
            'tax_amount' => $tax_amount,
            'corruption_percentage' => $HandlerCorruptionPercentage($corruption, $pay_tax),
            'corruption_nominal' => $corruption,
            'balance' => $HandlerBalanceAfterCorruption($project_value, $corruption)
        ], 200);
    }
});

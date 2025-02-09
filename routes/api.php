<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/try/{a}/{b}/{c}', function ($a, $b, $c) {
    /**
     * 
     * query params pake get (format: http://127.0.0.1:8000/api/try?a=1&b=2)
     * body params pake post (format: json,form data dst)
     * route params(path) all request methods (formatnya: http://127.0.0.1:8000/api/try/1/1)
     * 
     * request param: query,body,route(path)
     * 
     * {a}/{b} <- model binding untuk menyiapkan params input yang nanti nya akan di proses secara function
     */

    // $a = $request->query('a'); //req query artinya adalah requestnya hanya bisa melalui query params
    // $b = $request->query('b');

    // $a = $request->post('a'); //req body artinya adalah requestnya hanya bisa melalui body params
    // $b = $request->post('b');

    $c = $a + $b * $c;
    if (empty($a) || empty($b)) {
        return response()->json(['message' => 'input masih kosong']);
    }

    if ($c < 3) {
        $h = 'lebih kecil dari 3';
    } else {
        $h = 'lebih besar 3';
    }
    return response()->json(['message' => $h]);
});

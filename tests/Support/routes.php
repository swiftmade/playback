<?php

use Illuminate\Http\Request;
use Swiftmade\Idempotent\Idempotent;

Route::get('get', function () {
    return 'Get response ' . microtime();
});

Route::post('users', function () {
    return 'Created user id ' . uniqid();
})->middleware(Idempotent::class);

Route::post('books', function () {
    return 'Created book id ' . uniqid();
})->middleware(Idempotent::class);

Route::post('server_error', function () {
    abort(500, 'Internal server error ' . uniqid());
})->middleware(Idempotent::class);

Route::post('validate', function (Request $request) {
    $request->validate([
        'name' => 'required',
    ]);

    return response()->json([
        'validation' => 'ok',
    ]);
})->middleware(Idempotent::class);

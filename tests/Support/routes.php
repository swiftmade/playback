<?php

use Illuminate\Http\Request;
use Swiftmade\Playback\Playback;

Route::get('get', function () {
    return 'Get response ' . microtime();
});

Route::post('users', function () {
    return 'Created user id ' . uniqid();
})->middleware(Playback::class);

Route::post('books', function () {
    return 'Created book id ' . uniqid();
})->middleware(Playback::class);

Route::post('server_error', function () {
    abort(500, 'Internal server error ' . uniqid());
})->middleware(Playback::class);

Route::post('validate', function (Request $request) {
    $request->validate([
        'name' => 'required',
    ]);

    return response()->json([
        'validation' => 'ok',
    ]);
})->middleware(Playback::class);

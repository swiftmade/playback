<?php

use Swiftmade\Idempotent\IdempotentMiddleware;

Route::get('get', function () {
    return 'Get response ' . microtime();
});

Route::post('users', function () {
    return 'Created user id ' . uniqid();
})->middleware(IdempotentMiddleware::class);

Route::post('books', function () {
    return 'Created book id ' . uniqid();
})->middleware(IdempotentMiddleware::class);

<?php

use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Hello from Laravel API!',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

Route::get('/posts', function () {
    return response()->json([
        ['id' => 1, 'title' => '初めての投稿', 'content' => 'Laravel APIから取得したサンプルです。'],
        ['id' => 2, 'title' => 'Docker連携', 'content' => 'NuxtとLaravelがつながりました！'],
    ]);
});

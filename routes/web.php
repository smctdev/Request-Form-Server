<?php

use App\Http\Controllers\API\AttachmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/request-form-files/{filePath}', [AttachmentController::class, 'getFile'])->where('filePath', '.*');


<?php

use App\Http\Controllers\API\AttachmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::redirect('/', 'https://request.smctgroup.ph');

Route::get('/request-form-files/{filePath}', [AttachmentController::class, 'getFile'])->where('filePath', '.*');

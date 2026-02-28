<?php

use App\Http\Controllers\Api\EmailController;
use Illuminate\Support\Facades\Route;

Route::post('/email/generate', [EmailController::class, 'generate']);

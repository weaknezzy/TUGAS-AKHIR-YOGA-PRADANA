<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\PesananController;


Route::post('/midtrans/notification', [PesananController::class, 'notificationHandler']);
<?php

use App\Jobs\SimulateOrder;
use App\Jobs\SimulatePaymentUpdate;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
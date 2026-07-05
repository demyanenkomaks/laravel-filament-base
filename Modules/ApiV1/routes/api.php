<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\ApiV1\Http\Controllers\ApiV1Controller;

Route::get('', [ApiV1Controller::class, 'index'])->name('index');

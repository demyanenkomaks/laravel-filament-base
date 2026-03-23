<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('', fn (): array => [
    'message' => 'API check works!',
    'version' => '1.0.0',
]);

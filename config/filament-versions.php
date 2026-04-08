<?php

declare(strict_types=1);

use Filament\Support\Icons\Heroicon;
use Maksde\FilamentVersions\Providers\AppEnvVersionProvider;
use Maksde\FilamentVersions\Providers\FilamentVersionProvider;
use Maksde\FilamentVersions\Providers\LaravelVersionProvider;
use Maksde\FilamentVersions\Providers\MysqlVersionProvider;
use Maksde\FilamentVersions\Providers\PhpVersionProvider;
use Maksde\FilamentVersions\Providers\PostgresqlVersionProvider;
use Maksde\FilamentVersions\Providers\RedisVersionProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard / custom page widget
    |--------------------------------------------------------------------------
    |
    | Register VersionsWidget::class in your panel or page widgets.
    | `providers` is a list of VersionProvider class names in display order.
    |
    */
    'widget' => [
        'permission' => 'View:FilamentVersionsWidget',
        'column_span' => 'full',
        'sort' => null,
        'providers' => [
            PhpVersionProvider::class,
            LaravelVersionProvider::class,
            FilamentVersionProvider::class,
            // AppEnvVersionProvider::class,
            // MysqlVersionProvider::class,
            // PostgresqlVersionProvider::class,
            // RedisVersionProvider::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dedicated versions page
    |--------------------------------------------------------------------------
    */
    'page' => [
        'permission' => 'View:VersionsPage',
        'enabled' => true,
        'path' => 'versions',
        'should_register_navigation' => true,
        'navigation_sort' => 99,
        'navigation_icon' => Heroicon::OutlinedSquares2x2,
        'providers' => [
            PhpVersionProvider::class,
            LaravelVersionProvider::class,
            FilamentVersionProvider::class,
            AppEnvVersionProvider::class,
            MysqlVersionProvider::class,
            // PostgresqlVersionProvider::class,
            // RedisVersionProvider::class,
        ],
    ],

];

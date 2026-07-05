<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Пути к файлам-заглушкам в storage/app/public/default.
 *
 * Значения — относительно диска public (config/filesystems.php).
 * Используются в фабриках и сидерах вместо генерации бинарных данных.
 */
final class DefaultStoragePath
{
    /** Каталог на диске public. */
    public const string DIRECTORY = 'default';

    /** Изображение JPEG. */
    public const string LARAVEL_JPG = self::DIRECTORY.'/laravel.jpg';

    /** Видео MP4. */
    public const string LARAVEL_MP4 = self::DIRECTORY.'/laravel.mp4';

    /** Изображение PNG. */
    public const string LARAVEL_PNG = self::DIRECTORY.'/laravel.png';

    /** Изображение SVG. */
    public const string LARAVEL_SVG = self::DIRECTORY.'/laravel.svg';

    /** Видео WebM. */
    public const string LARAVEL_WEBM = self::DIRECTORY.'/laravel.webm';

    private function __construct() {}

    /**
     * Пути к изображениям для fake()->randomElement().
     *
     * @return list<string>
     */
    public static function images(): array
    {
        return [
            self::LARAVEL_JPG,
            self::LARAVEL_PNG,
            self::LARAVEL_SVG,
        ];
    }

    /**
     * Пути к видео для fake()->randomElement().
     *
     * @return list<string>
     */
    public static function videos(): array
    {
        return [
            self::LARAVEL_MP4,
            self::LARAVEL_WEBM,
        ];
    }

    /**
     * Все доступные файлы-заглушки.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::LARAVEL_JPG,
            self::LARAVEL_MP4,
            self::LARAVEL_PNG,
            self::LARAVEL_SVG,
            self::LARAVEL_WEBM,
        ];
    }

    /**
     * Абсолютный путь к файлу на диске (storage/app/public/...).
     */
    public static function absolute(string $path): string
    {
        return storage_path('app/public/'.$path);
    }
}

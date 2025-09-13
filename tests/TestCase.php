<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function filePath(string $filename): string
    {
        return __DIR__ . '/_files/' . $filename;
    }

    public static function fileContents(string $filename): string
    {
        return (string) file_get_contents(static::filePath($filename));
    }

    public function getenv(string $variableName, string $default = ''): string
    {
        if (! isset($_SERVER[$variableName])) {
            return $default;
        }

        $value = $_SERVER[$variableName];
        if (! is_scalar($value)) {
            return $default;
        }

        return strval($value);
    }

    public function relativeToAbsolutePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }
        return __DIR__ . '/../' . $path;
    }
}

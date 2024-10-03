<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Tests\Unit;

use LogicException;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Settings;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Tests\TestCase;

final class SettingsTest extends TestCase
{
    /** @return array<string, array{array<string, int|string>}> */
    public static function providerCreateSettingsWithInvalidValue(): array
    {
        return [
            'width 0' => [['width' => 0]],
            'width -1' => [['width' => -1]],
            'height 0' => [['height' => 0]],
            'height -1' => [['height' => -1]],
            'vocab empty' => [['vocab' => '']],
            'model_path empty' => [['model_path' => '']],
        ];
    }

    /**
     * @param array{array<string, int|string>} $values
     * @dataProvider providerCreateSettingsWithInvalidValue
     */
    public function testCreateSettingsWithInvalidValue(array $values): void
    {
        $values = $values + [
            'width' => 160,
            'height' => 60,
            'vocab' => 'FOOBAR',
            'model_path' => 'the-model.onnx',
        ];
        $this->expectException(LogicException::class);
        new Settings(
            $values['width'],
            $values['height'],
            $values['vocab'],
            $values['model_path'],
        );
    }
}

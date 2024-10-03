<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Tests\Unit;

use LogicException;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\ConfigsReader;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Tests\TestCase;
use RuntimeException;

final class ConfigsReaderTest extends TestCase
{
    public function testSettingsFromFile(): void
    {
        $reader = new ConfigsReader();
        /** @see tests/_files/configs-sample.yml */
        $settings = $reader->settingsFromFile($this->filePath('configs-sample.yml'));
        $this->assertSame(100, $settings->imageWidth);
        $this->assertSame(50, $settings->imageHeight);
        $this->assertSame($this->filePath('x-model.onnx'), $settings->onnxModel);
        $this->assertSame('FOOBAR', $settings->alphabet);
    }

    public function testSettingsFromEmptyFile(): void
    {
        $reader = new ConfigsReader();
        $this->expectException(LogicException::class);
        /** @see tests/_files/configs-empty.yml */
        $reader->settingsFromFile($this->filePath('configs-empty.yml'));
    }

    public function testSettingsFromFileWhenFileIsNotYamlThrowsException(): void
    {
        $reader = new ConfigsReader();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to parse BoxFactura AI model config file');
        $reader->settingsFromFile(__FILE__);
    }
}

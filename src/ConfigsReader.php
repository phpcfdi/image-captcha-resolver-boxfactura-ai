<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\BoxFacturaAI;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final class ConfigsReader
{
    public function settingsFromFile(string $configsFile): Settings
    {
        try {
            $configs = Yaml::parseFile($configsFile);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                sprintf('Unable to parse BoxFactura AI model config file from %s', $configsFile),
                previous: $exception
            );
        }

        return new Settings(
            intval($this->scalarFromArray($configs, 'width')),
            intval($this->scalarFromArray($configs, 'height')),
            strval($this->scalarFromArray($configs, 'vocab')),
            sprintf('%s/%s.onnx', dirname($configsFile), $this->scalarFromArray($configs, 'model_path')),
        );
    }

    /**
     * @return scalar
     * @noinspection PhpMissingReturnTypeInspection
     */
    private function scalarFromArray(mixed $array, string $key)
    {
        if (is_array($array) && isset($array[$key]) && is_scalar($array[$key])) {
            return $array[$key];
        }

        return '';
    }
}

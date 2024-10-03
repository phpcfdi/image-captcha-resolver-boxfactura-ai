<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\BoxFacturaAI;

use InvalidArgumentException;

final class Settings
{
    /** @var list<string> */
    public array $alphabetArray;

    public function __construct(
        public readonly int $imageWidth,
        public readonly int $imageHeight,
        public readonly string $alphabet,
        public readonly string $onnxModel,
    ) {
        if ($this->imageWidth <= 0) {
            throw new InvalidArgumentException('Image width cannot be lower than zero');
        }
        if ($this->imageHeight <= 0) {
            throw new InvalidArgumentException('Image height cannot be lower than zero');
        }
        if ('' === $this->alphabet) {
            throw new InvalidArgumentException('Alphabet cannot be an empty string');
        }
        if ('' === $this->onnxModel) {
            throw new InvalidArgumentException('Onnx model path cannot be an empty string');
        }
        $this->alphabetArray = str_split($this->alphabet);
    }
}

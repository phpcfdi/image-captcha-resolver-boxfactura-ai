<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\BoxFacturaAI;

use Imagine\Exception\RuntimeException;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\Color\RGB as Color;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use OnnxRuntime\InferenceSession;

final class Processor
{
    /** @param list<string> $alphabet */
    public function __construct(
        public readonly int $imageWidth,
        public readonly int $imageHeight,
        public readonly array $alphabet,
        public readonly InferenceSession $session,
        public readonly ImagineInterface $imagine
    ) {
    }

    public static function createFromSettings(Settings $settings): self
    {
        return new self(
            $settings->imageWidth,
            $settings->imageHeight,
            $settings->alphabetArray,
            new InferenceSession($settings->onnxModel),
            new Imagine()
        );
    }

    /** @throws RuntimeException */
    public function resolveImageFile(string $imagePath): string
    {
        $image = $this->imagine->open($imagePath);
        return $this->resolveImage($image);
    }

    /** @throws RuntimeException */
    public function resolveImageContent(string $image): string
    {
        $image = $this->imagine->load($image);
        return $this->resolveImage($image);
    }

    public function resolveImage(ImageInterface $image): string
    {
        $input = $this->imageToPixelsShape($image);
        $output = $this->session->run(null, ['input' => [$input]]);
        return $this->logitsToText($output[0]);
    }

    /**
     * @throws RuntimeException
     * @return list<list<array{int, int, int}>>
     */
    private function imageToPixelsShape(ImageInterface $image): array
    {
        // remove alpha
        $palette = new RGB();
        $white = $palette->color('ffffff', /* alpha: */ 0);
        $flattened = $this->imagine->create($image->getSize(), $white);
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $image = $flattened->paste($image, new Point(0, 0), /* alpha: */ 100);

        // resize
        $image = $image->resize(new Box($this->imageWidth, $this->imageHeight));

        // shape
        $shape = [];
        for ($y = 0; $y < $this->imageHeight; $y++) {
            $row = [];
            for ($x = 0; $x < $this->imageWidth; $x++) {
                /** @var Color $pixel */
                $pixel = $image->getColorAt(new Point($x, $y));
                $row[] = [$pixel->getRed(), $pixel->getGreen(), $pixel->getBlue()];
            }
            $shape[] = $row;
        }

        return $shape;
    }

    /** @param array<array<list<float>>> $output */
    private function logitsToText(array $output): string
    {
        $result = [];
        foreach ($output as $logitsArray) {
            foreach ($logitsArray as $logits) {
                $probabilities = $this->softmax($logits);
                $maxIndex = $this->maxIndex($probabilities);
                $predicted = $this->alphabet[$maxIndex] ?? '';
                if ([] === $result || $predicted !== end($result)) {
                    $result[] = $predicted;
                }
            }
        }
        return implode('', $result);
    }

    /**
     * @see https://en.wikipedia.org/wiki/Softmax_function
     * @param float[] $values
     * @return float[]
     */
    private function softmax(array $values): array
    {
        $values = array_map(fn (float $value): float => exp($value), $values);
        $sum = array_sum($values);
        return array_map(fn (float $value): float => $value / $sum, $values);
    }

    /**
     * Get the index of the greatest element, return -1 when not found
     * The usage of max and array_search on floats can be buggy.
     *
     * @param list<float> $values
     * @return int
     */
    private function maxIndex(array $values): int
    {
        $maxIndex = null;
        $maxValue = -INF;
        foreach ($values as $index => $value) {
            if ($value > $maxValue) {
                $maxIndex = $index;
                $maxValue = $value;
            }
        }
        return $maxIndex ?? -1;
    }
}

<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\BoxFacturaAI;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;
use Throwable;

final class BoxFacturaAIResolver implements CaptchaResolverInterface
{
    public function __construct(
        public readonly Processor $processor,
    ) {
    }

    public static function createFromConfigs(string $configsFile): self
    {
        $reader = new ConfigsReader();
        $settings = $reader->settingsFromFile($configsFile);
        return self::createFromSettings($settings);
    }

    public static function createFromSettings(Settings $settings): self
    {
        $runtime = Processor::createFromSettings($settings);
        return new self($runtime);
    }

    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        try {
            // perform AI
            $result = $this->processor->resolveImageContent($image->asBinary());
            if (6 !== strlen($result)) {
                throw new UnableToResolveCaptchaException($this, $image);
            }

            return new CaptchaAnswer($result);
        } catch (Throwable $exception) {
            throw new UnableToResolveCaptchaException($this, $image, $exception);
        }
    }

    public function getProcessor(): Processor
    {
        return $this->processor;
    }
}

<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Tests\Integration;

use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\BoxFacturaAIResolver;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;

final class BoxFacturaAIResolverUsageTest extends TestCase
{
    private BoxFacturaAIResolver $boxFacturaAIResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $configsFile = $this->getenv('BOXFACTURA_AI_RESOLVER_CONFIGS');
        $configsFile = $this->relativeToAbsolutePath($configsFile);
        if (! file_exists($configsFile) || is_dir($configsFile)) {
            $this->fail(sprintf('Unable to see BoxFactura AI model config file from %s', $configsFile));
        }

        $this->boxFacturaAIResolver = BoxFacturaAIResolver::createFromConfigs($configsFile);
    }

    public function testCaptchaResolution(): void
    {
        /** @see tests/_files/BK22PD.png */
        $sampleFile = $this->filePath('BK22PD.png');
        $captcha = CaptchaImage::newFromFile($sampleFile);
        $expectedAnswer = new CaptchaAnswer('BK22PD');

        $answer = $this->boxFacturaAIResolver->resolve($captcha);

        $this->assertTrue($answer->equalsTo($expectedAnswer));
    }

    public function testCaptchaResolutionFile(): void
    {
        /** @see tests/_files/BK22PD.png */
        $sampleFile = $this->filePath('BK22PD.png');
        $expectedAnswer = new CaptchaAnswer('BK22PD');

        $processor = $this->boxFacturaAIResolver->getProcessor();
        $answerText = $processor->resolveImageFile($sampleFile);
        $answer = new CaptchaAnswer($answerText);

        $this->assertTrue($answer->equalsTo($expectedAnswer));
    }

    public function testCaptchaResolveUsingNonImage(): void
    {
        /** @see tests/_files/red-pixel.gif */
        $sampleFile = $this->filePath('red-pixel.gif');
        $captcha = CaptchaImage::newFromFile($sampleFile);

        $this->expectException(UnableToResolveCaptchaException::class);
        $this->expectExceptionMessage('Unable to resolve captcha image');
        $this->boxFacturaAIResolver->resolve($captcha);
    }
}

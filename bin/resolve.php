#!/usr/bin/env php
<?php

declare(strict_types=1);

use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\ConfigsReader;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Processor;

require __DIR__ . '/../vendor/autoload.php';

exit(call_user_func(new class () {
    private function printHelp(string $command): void
    {
        $command = basename($command);
        echo <<< END_OF_HELP
            $command - Resolve a captcha
            Syntax:
                $command <catpchas> [-c|--config config-file]
            Arguments:
                <captchas>
                    List of files to solve
                -c|--config config-file
                    Define the location where the configs.yaml is.
            END_OF_HELP, PHP_EOL, PHP_EOL;
    }

    public function __invoke(string $command, string ...$arguments): int
    {
        if ([] !== array_intersect($arguments, ['help', '--help', '-h'])) {
            $this->printHelp($command);
            return 0;
        }
        try {
            $this->execute(...$arguments);
            return 0;
        } catch (Throwable $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
            return 1;
        }
    }

    private function execute(string ...$arguments): void
    {
        $configsFile = '';
        $captchaFiles = [];
        $it = new ArrayIterator($arguments);
        foreach ($it as $argument) {
            if (in_array($argument, ['-c', '--config'])) {
                $it->next();
                $configsFile = (string) $it->current();
                continue;
            }
            $captchaFiles[] = $argument;
        }
        if ('' === $configsFile) {
            throw new Exception('Config file was not set');
        }
        if ([] === $captchaFiles) {
            throw new Exception('Captcha files were not set');
        }
        $captchaFiles = array_unique($captchaFiles);

        $reader = new ConfigsReader();
        $settings = $reader->settingsFromFile($configsFile);
        $processor = Processor::createFromSettings($settings);

        foreach ($captchaFiles as $captchaFile) {
            try {
                $result = $processor->resolveImageFile($captchaFile);
            } catch (Throwable $exception) {
                $result = '[ERROR] ' . $exception->getMessage();
            }
            echo $captchaFile, ': ', $result, PHP_EOL;
        }
    }
}, ...$argv));

<?php

namespace Jellydock\JellypdfBundle\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PdfGenerator
{
    private string $binaryPath;
    private string $defaultFormat;
    private string $defaultEngine;

    public function __construct(string $binaryPath, string $defaultFormat, string $defaultEngine)
    {
        $this->binaryPath = $binaryPath;
        $this->defaultFormat = $defaultFormat;
        $this->defaultEngine = $defaultEngine;
    }

    public function generate(string $input, ?string $output = null, array $options = []): void
    {
        $args = [
            $this->binaryPath,
            $input
        ];

        if ($output) {
            $args[] = $output;
        }

        $options = array_merge([
            '--format' => $this->defaultFormat,
            '--engine' => $this->defaultEngine,
        ], $options);

        foreach ($options as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $args[] = is_numeric($key) ? $value : $key;
                }
            } else {
                $args[] = $key;
                $args[] = (string)$value;
            }
        }

        $process = new Process($args);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
<?php

namespace Jellydock\JellypdfBundle\Service;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PdfGenerator
{
    public function __construct(
        private string $binaryPath, 
        private string $defaultFormat, 
        private string $defaultEngine
    ){
        if (!is_executable($this->binaryPath)) {
            throw new \RuntimeException(sprintf(
                "jellypdf-cli not found or not executable at '%s'.\n\n➡️ Please run: npm install -g jellypdf-cli\n",
                $this->binaryPath
            ));
        }
    }

    public function generateFromHtml(string $html, ?string $outputPath = null, array $options = []): ?string
    {
        $tmpInput = tempnam(sys_get_temp_dir(), 'jellypdf_input_').'.html';
        file_put_contents($tmpInput, $html);

        $bufferMode = $outputPath === null;
        $tmpOutput = $bufferMode ? tempnam(sys_get_temp_dir(), 'jellypdf_output_').'.pdf' : $outputPath;

        $this->generate($tmpInput, $tmpOutput, $options);

        @unlink($tmpInput);

        if ($bufferMode) {
            $pdf = file_get_contents($tmpOutput);
            @unlink($tmpOutput);
            return $pdf;
        }

        return null;
    }

    public function generate(string $input, ?string $output = null, array $options = []): void
    {
        $args = [ $this->binaryPath, $input ];

        if ($output) {
            $args[] = $output;
        }

        $options = array_merge([
            '--format' => $this->defaultFormat,
            '--engine' => $this->defaultEngine,
        ], $options);

        foreach ($options as $key => $value)
        {
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
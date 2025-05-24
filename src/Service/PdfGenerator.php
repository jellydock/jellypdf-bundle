<?php

namespace Jellydock\JellypdfBundle\Service;

class PdfGenerator
{
    private string $binaryPath;

    public function __construct(string $binaryPath)
    {
        $this->binaryPath = $binaryPath;

        if (!is_executable($this->binaryPath)) {
            throw new \RuntimeException(sprintf(
                "jellypdf-cli not found or not executable at '%s'.\n\nPlease run: npm install -g jellypdf-cli\n",
                $this->binaryPath
            ));
        }
    }

    public function generateFromHtml(string $html, ?string $outputPath = null, array $options = []): ?string
    {
        $tmpInput = sprintf('%s.html', tempnam(sys_get_temp_dir(), 'jellypdf_input_'));

        file_put_contents($tmpInput, $html);

        $bufferMode = $outputPath === null;

        $tmpOutput = $bufferMode 
            ? sprintf('%s.pdf', tempnam(sys_get_temp_dir(), 'jellypdf_output_')) 
            : $outputPath
        ;

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
        $cmd = implode(' ', [
            escapeshellcmd($this->binaryPath),
            escapeshellarg($input),
            escapeshellarg($output),
            $this->buildCommandOptions($options)
        ])

        exec(sprintf('%s 2>&1', $cmd), $outputLines, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException(sprintf(
                'jellypdf-cli failed with exit code %s:\n%s', 
                $exitCode, 
                implode("\n", $outputLines)
            ));
        }
    }

    private function buildCommandOptions(array $options): array
    {
        return implode(' ', array_reduce(array_keys($options), function(array $carry, string $key) use ($options) {

            $value = $options[$key];

            if (is_bool($value)) {

                if ($value) {
                    $carry[] = escapeshellarg($key);
                }

            } else {

                $carry[] = escapeshellarg($key);
                $carry[] = escapeshellarg((string)$value);
            }

            return $carry;

        }, []));
    }
}
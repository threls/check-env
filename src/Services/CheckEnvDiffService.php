<?php

namespace Threls\ThrelsCheckEnv\Services;

use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

class CheckEnvDiffService
{
    private $data;

    private $table;

    private $output;

    public $config;

    public function __construct()
    {
        $this->config = config('check-env');

        $this->table = new Table(
            $this->output = new BufferedOutput
        );
    }

    public function add($file): void
    {
        $files = is_array($file) ? $file : [$file];

        foreach ($files as $envFile) {
            $this->setData(
                $envFile,
                Dotenv::createMutable($this->getPath(), $envFile)->load()
            );
        }
    }

    private function getPath(): string
    {
        return $this->config['path'] ?? base_path();
    }

    public function setData(string $file, array $data): void
    {
        $this->data[$file] = $data;
    }

    public function getData(?string $file = null): array
    {
        if ($file === null) {
            return $this->data;
        }

        return $this->data[$file] ?? [];
    }

    public function diff(): array
    {
        $variables = [];

        foreach ($this->data as $file => $vars) {
            foreach ($vars as $key => $value) {
                if (in_array($key, $variables, false)) {
                    continue;
                }

                $variables[] = $key;
            }
        }

        $diff = [];

        foreach ($variables as $variable) {
            $containing = [];

            foreach ($this->data as $file => $vars) {
                $containing[$file] = array_key_exists($variable, $vars);
            }

            $unique = array_unique(array_values($containing));

            if (count($unique) === 1 && $unique[0] === true) {
                continue;
            }

            $diff[$variable] = $containing;
        }

        return $diff;
    }

    public function buildTable(): void
    {
        $files = array_keys($this->data);

        $headers = ['Variable'];

        foreach ($files as $file) {
            $headers[] = $file;
        }

        $this->table->setHeaders($headers);

        $showValues = $this->config['show_values'] ?? false;

        foreach ($this->diff() as $variable => $containing) {
            $row = [$variable];

            foreach ($files as $file) {
                $value = null;

                if (! $showValues) {
                    $value = $this->valueNotFound();

                    if ($containing[$file] === true) {
                        $value = $this->valueOkay();
                    }
                } else {
                    $value = '<fg=red> MISSING </>';

                    $existing = $this->getData($file)[$variable] ?? null;

                    if ($existing !== null) {
                        $value = $existing;
                    }
                }

                $row[] = $value;
            }

            $this->table->addRow($row);
        }
    }

    public function displayTable(): void
    {
        $this->buildTable();

        $this->table->render();

        echo $this->output->fetch();
    }

    private function valueOkay(): string
    {
        return '<fg=green> Y </>';
    }

    private function valueNotFound(): string
    {
        return '<fg=red> N </>';
    }
}

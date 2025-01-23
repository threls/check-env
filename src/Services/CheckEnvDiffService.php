<?php

namespace Threls\ThrelsCheckEnv\Services;

use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

class CheckEnvDiffService
{
    private array $data = [];

    private Table $table;

    private BufferedOutput $output;

    private array $config;

    public array $diff = [];

    public function __construct()
    {
        $this->config = config('check-env');
        $this->output = new BufferedOutput;
        $this->table = new Table($this->output);
    }

    public function add($file): void
    {
        $files = is_array($file) ? $file : [$file];

        foreach ($files as $envFile) {
            $this->data[$envFile] = Dotenv::createMutable(base_path(), $envFile)->load();
        }
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
        //        $variables = [];
        //
        //        foreach ($this->data as $file => $vars) {
        //            foreach ($vars as $key => $value) {
        //                if (in_array($key, $variables, false)) {
        //                    continue;
        //                }
        //
        //                $variables[] = $key;
        //            }
        //        }

        $variables = array_unique(array_merge(...array_map('array_keys', $this->data)));

        foreach ($variables as $variable) {
            //            $containing = [];
            //
            //            foreach ($this->data as $file => $vars) {
            //                $containing[$file] = array_key_exists($variable, $vars);
            //            }
            //
            //            $unique = array_unique(array_values($containing));
            //
            //            if (1 === count($unique) && true === $unique[0]) {
            //                continue;
            //            }
            //
            //            $this->diff[$variable] = $containing;

            $containing = array_map(
                fn ($vars) => array_key_exists($variable, $vars),
                $this->data
            );

            $unique = array_unique($containing);

            if (count($unique) === 1 && reset($unique) === true) {
                continue;
            }

            $this->diff[$variable] = array_combine(array_keys($this->data), $containing);

        }

        return $this->diff;
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

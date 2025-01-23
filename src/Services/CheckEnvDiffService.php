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
        return $file === null ? $this->data : ($this->data[$file] ?? []);
    }

    public function diff(): array
    {
        $allVariables = $this->getAllVariables();

        foreach ($allVariables as $variable) {
            $filePresence = $this->getVariablePresenceAcrossFiles($variable);

            if (count(array_unique($filePresence)) > 1) {
                $this->diff[$variable] = $filePresence;
            }
        }

        return $this->diff;
    }

    private function getAllVariables(): array
    {
        return array_unique(array_merge(...array_map('array_keys', $this->data)));
    }

    private function getVariablePresenceAcrossFiles(string $variable): array
    {
        $presence = [];

        foreach ($this->data as $file => $vars) {
            $presence[$file] = array_key_exists($variable, $vars);
        }

        return $presence;
    }

    public function displayTable(): void
    {
        $this->buildTable();
        $this->table->render();
        echo $this->output->fetch();
    }

    private function buildTable(): void
    {
        $headers = ['Variable', ...array_keys($this->data)];
        $this->table->setHeaders($headers);

        foreach ($this->diff() as $variable => $filePresence) {
            $this->table->addRow($this->buildRow($variable, $filePresence));
        }
    }

    private function buildRow(string $variable, array $filePresence): array
    {
        $showValues = $this->config['show_values'] ?? false;
        $row = [$variable];

        foreach ($filePresence as $file => $exists) {
            $row[] = $showValues
                ? ($this->getData($file)[$variable] ?? '<fg=red> MISSING </>')
                : ($exists ? $this->valueOkay() : $this->valueNotFound());
        }

        return $row;
    }

    private function valueOkay(): string
    {
        return '<fg=green>Y</>';
    }

    private function valueNotFound(): string
    {
        return '<fg=red>N</>';
    }
}

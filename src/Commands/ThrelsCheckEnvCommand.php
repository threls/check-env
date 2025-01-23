<?php

namespace Threls\ThrelsCheckEnv\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Threls\ThrelsCheckEnv\Services\CheckEnvDiffService;

class ThrelsCheckEnvCommand extends Command
{
    public $signature = 'check-env';

    public $description = 'Check encrypted environments if they are the same as decrypted environment. ';

    public bool $failure = false;

    public Repository $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('check-env');
    }

    public function handle()
    {
        $this->info('Starting environment file validation...');
        $this->info('Step 1: Check differences between decrypted environments...');

        $this->checkDiffBetweenEnvs();

        $suffix = $this->config->get('check-env.temp-env-suffix');

        $environments = $this->config->get('check-env.environments');

        foreach ($environments as $env => $key) {
            $this->info("Checking environment: $env");
            $this->copyEncryptedFile($env, $suffix);
            $this->decryptEnvFile($env, $key['encryption-key'], $suffix);
            $this->compareEnvFiles($env, $suffix);
        }

        return $this->checkFailure();

    }

    protected function copyEncryptedFile(string $env, string $suffix)
    {
        $encryptedFile = base_path(".env.$env.encrypted");
        $testEncryptedFile = base_path(".env.$env.$suffix.encrypted");

        if (! File::exists($encryptedFile)) {
            $this->error("Encrypted file not found: $encryptedFile");

            $this->failure = true;

            return;
        }

        File::copy($encryptedFile, $testEncryptedFile);
        $this->info("Copied $encryptedFile to $testEncryptedFile.");
    }

    protected function decryptEnvFile(string $env, string $key, string $suffix)
    {
        $decryptCommand = "php artisan env:decrypt --env=$env.test --key=$key";
        $output = null;
        $resultCode = null;

        exec($decryptCommand, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error("Failed to decrypt the .env.$env.$suffix.encrypted file.");

            $this->failure = true;

            return;
        }

        $this->info("Decrypted .env.$env.$suffix.encrypted successfully.");
    }

    protected function compareEnvFiles(string $env, string $suffix)
    {
        $envFile = base_path(".env.$env");
        $testEnvFile = base_path(".env.$env.$suffix");
        $testEncryptedFile = base_path(".env.$env.$suffix.encrypted");

        if (! File::exists($envFile) || ! File::exists($testEnvFile)) {
            $this->error("One or both files are missing: $envFile, $testEnvFile");
            $this->failure = true;

            return;
        }

        $envContent = collect(File::lines($envFile))->sort()->implode("\n");
        $decryptedContent = collect(File::lines($testEnvFile))->sort()->implode("\n");

        File::delete($testEnvFile);
        File::delete($testEncryptedFile);

        if ($envContent !== $decryptedContent) {
            $this->error("The .env.$env and decrypted .env.$env.encrypted do not match.");

            $this->failure = true;

            return;
        }

        $this->info("Success: .env.$env and the decrypted .env.$env.encrypted match.");

    }

    protected function checkDiffBetweenEnvs(): void
    {
        $files = config('check-env.files') ?: ['.env'];

        if ($overrideFiles = $this->argument('files')) {
            $files = explode(',', $overrideFiles);
        }

        $service = new CheckEnvDiffService;

        $service->add($files);

        $service->displayTable();

        if (! empty($service->diff)) {
            $this->error('You have missing variables between your env files.');
            $this->failure = true;
        } else {
            $this->info('No differences between your env files.');
        }
    }

    protected function checkFailure()
    {
        if ($this->failure) {
            return self::FAILURE;
        } else {
            $this->info("\nEnvironment file validation completed successfully for all environments.");

            return self::SUCCESS;
        }

    }
}

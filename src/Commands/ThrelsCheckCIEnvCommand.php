<?php

namespace Threls\ThrelsCheckEnv\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Threls\ThrelsCheckEnv\Services\CheckEnvDiffService;

class ThrelsCheckCIEnvCommand extends Command
{
    public $signature = 'check-env-ci {--env=} {--key=}';

    public $description = 'Check encrypted environments if they are the same as decrypted environment. ';

    public bool $failure = false;

    public function handle()
    {
        $this->info('Starting environment validation...');

        $this->checkDiffBetweenEnvs();

        return $this->checkFailure();

    }

    protected function checkDiffBetweenEnvs(): void
    {
        $suffix = config('check-env.temp-env-suffix');
        $environment = $this->option('env');
        $key = $this->option('key');

        if (!$environment) {
            $this->error('Environment not specified');
        }


        $this->copyEncryptedFile($environment, $suffix);
        $this->decryptEnvFile($environment, $key, $suffix);

        $testEnvFile = base_path(".env.$environment.$suffix");
        $testEncryptedFile = base_path(".env.$environment.$suffix.encrypted");

        if (!File::exists($testEnvFile)) {
            $this->error("File is missing: $testEnvFile");
            $this->failure = true;

            return;
        }

        $files = [
            '.env.example',
            ".env.$environment.$suffix",
        ];

        $service = new CheckEnvDiffService;

        $service->add($files);

        $service->displayTable();

        if (!empty($service->diff)) {
            $this->error('You have missing variables between your env files.');
            $this->failure = true;
        } else {
            $this->info('No differences between your env files.');
        }

        File::delete($testEnvFile);
        File::delete($testEncryptedFile);
    }

    protected function copyEncryptedFile(string $env, string $suffix)
    {
        $encryptedFile = base_path(".env.$env.encrypted");
        $testEncryptedFile = base_path(".env.$env.$suffix.encrypted");

        if (!File::exists($encryptedFile)) {
            $this->error("Encrypted file not found: $encryptedFile");

            $this->failure = true;

            return;
        }

        File::copy($encryptedFile, $testEncryptedFile);
    }

    protected function decryptEnvFile(string $env, string $key, string $suffix)
    {
        $decryptCommand = "php artisan env:decrypt --env=$env.$suffix --key=$key";
        $output = null;
        $resultCode = null;

        exec($decryptCommand, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error("Failed to decrypt the .env.$env.$suffix.encrypted file.");

            $this->failure = true;

        }

    }

    protected function checkFailure()
    {
        if ($this->failure) {
            return self::FAILURE;
        } else {
            $this->info("\nEnvironment file validation completed successfully.");

            return self::SUCCESS;
        }

    }
}


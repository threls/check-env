<?php

namespace Threls\ThrelsCheckEnv\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThrelsCheckEnvCommand extends Command
{
    public $signature = 'check-env';

    public $description = 'My command';

    public function handle()
    {
        $this->info('Starting environment file validation...');

        $suffix = config('check-env.temp-env-suffix');

        $environments = config('check-env.environments');

        foreach ($environments as $env => $key) {
            $this->info("Checking environment: $env");
            $this->copyEncryptedFile($env, $suffix);
            $this->decryptEnvFile($env, $key['encryption-key'], $suffix);
            $this->compareEnvFiles($env, $suffix);
        }

        $this->info("\nEnvironment file validation completed successfully for all environments.");

        return self::SUCCESS;
    }

    protected function copyEncryptedFile(string $env, string $suffix)
    {
        $encryptedFile = base_path(".env.$env.encrypted");
        $testEncryptedFile = base_path(".env.$env.$suffix.encrypted");

        if (! File::exists($encryptedFile)) {
            $this->error("Encrypted file not found: $encryptedFile");

            return self::FAILURE;
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

            return self::FAILURE;
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

            return self::FAILURE;
        }

        $envContent = collect(File::lines($envFile))->sort()->implode("\n");
        $decryptedContent = collect(File::lines($testEnvFile))->sort()->implode("\n");

        File::delete($testEnvFile);
        File::delete($testEncryptedFile);

        if ($envContent !== $decryptedContent) {
            $this->error("The .env.$env and decrypted .env.$env.encrypted do not match.");

            return self::FAILURE;
        }

        $this->info("Success: .env.$env and the decrypted .env.$env.encrypted match.");

    }
}

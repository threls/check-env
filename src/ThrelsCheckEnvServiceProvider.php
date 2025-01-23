<?php

namespace Threls\ThrelsCheckEnv;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Threls\ThrelsCheckEnv\Commands\ThrelsCheckEnvCommand;

class ThrelsCheckEnvServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('check-env')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_check_env_table')
            ->hasCommand(ThrelsCheckEnvCommand::class);
    }
}

<?php

namespace Threls\ThrelsCheckEnv;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Threls\ThrelsCheckEnv\Commands\ThrelsCheckCIEnvCommand;
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
            ->hasCommands(ThrelsCheckEnvCommand::class, ThrelsCheckCIEnvCommand::class);

    }
}

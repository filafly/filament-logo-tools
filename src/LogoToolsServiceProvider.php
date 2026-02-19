<?php

namespace Filafly\LogoTools;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LogoToolsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filafly-logo-tools');
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('logo-tools', __DIR__ . '/../resources/css/logo-tools.css'),
        ], 'filafly/filament-logo-tools');
    }
}

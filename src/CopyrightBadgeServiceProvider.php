<?php

declare(strict_types=1);

namespace Codava\CopyrightBadge;

use Codava\CopyrightBadge\View\Badge;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class CopyrightBadgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/copyright-badge.php', 'copyright-badge');
    }

    public function boot(): void
    {
        Blade::component('copyright-badge', Badge::class);

        $this->publishes([__DIR__.'/../config/copyright-badge.php' => config_path('copyright-badge.php')], 'copyright-badge-config');
    }
}

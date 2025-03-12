<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\RemarkService;
use App\Services\RemarkServiceInterface;
use Illuminate\Support\ServiceProvider;

final class RemarkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RemarkServiceInterface::class, RemarkService::class);
    }
}

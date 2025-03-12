<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\RemarkService;
use App\Services\RemarkServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        RemarkServiceInterface::class => RemarkService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::macro('toSqlWithBindings', function () {
            $bindings = array_map(
                fn($value) => is_numeric($value) ? $value : "'{$value}'",
                $this->getBindings()
            );

            return Str::replaceArray('?', $bindings, $this->toSql());
        });
    }
}

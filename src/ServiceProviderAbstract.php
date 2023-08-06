<?php

namespace ResourceWizard;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ServiceProviderAbstract extends ServiceProvider
{
    final public function boot(
        Router $router,
        AuthManager $auth,
        Gate $gate,
    ): self {
        $this->mergeConfigFrom(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'config', 'resource-wizard.php']), 'resource-wizard');
        $this->publishes([implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'config', 'resource-wizard.php']) => config_path('resource-wizard.php')], 'resource-wizard');

        $this->registerMiddleware($router);
        $this->registerRoutes();

        return $this;
    }

    final public function provides(): array
    {
        return [

        ];
    }

    final public function register(): self
    {
        $this->registerGuards();

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    final public function registerGuards(): void
    {

    }

    /**
     * @codeCoverageIgnore
     */
    final public function registerMiddleware(
        Router $router,
    ): void {

    }

    final public function registerRoutes(): void
    {

    }
}

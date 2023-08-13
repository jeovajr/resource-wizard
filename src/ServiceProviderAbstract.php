<?php

namespace ResourceWizard;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use ResourceWizard\Console\Build;
use ResourceWizard\Console\Create;
use ResourceWizard\Events\EventAdd;
use ResourceWizard\Events\EventBrowse;
use ResourceWizard\Events\EventDelete;
use ResourceWizard\Events\EventEdit;
use ResourceWizard\Events\EventLock;
use ResourceWizard\Events\EventRead;
use ResourceWizard\Events\EventUnlock;
use ResourceWizard\Facade\ResourceWizard;
use ResourceWizard\Models\ResourceModel;
use ResourceWizard\Requests\FormRequest;
use ResourceWizard\Services\ResourceWizard as ResourceWizardService;

class ServiceProviderAbstract extends ServiceProvider implements DeferrableProvider
{
    final public function boot(
        Router $router,
        AuthManager $auth,
        Gate $gate,
    ): self {
        $this->mergeConfigFrom(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'config', 'resource-wizard.php']), 'resource-wizard');
        $this->publishes([implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'config', 'resource-wizard.php']) => config_path('resource-wizard.php')], 'resource-wizard');

        $this->registerCommands();

        $this->registerMiddleware($router);
        $this->registerRoutes();

        return $this;
    }

    /**
     * Register the console commands for the package.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Create::class,
                Build::class,
            ]);
        }
    }

    final public function provides(): array
    {
        return [
            ResourceWizardService::class,
            ResourceWizard::class,
            ResourceModel::class,
            EventBrowse::class,
            EventRead::class,
            EventEdit::class,
            EventAdd::class,
            EventDelete::class,
            EventLock::class,
            EventUnlock::class,
            FormRequest::class,
        ];
    }

    final public function register(): self
    {
        $this->registerGuards();

        $this->app->singleton(ResourceWizard::class, fn () => new ResourceWizardService());

        $this->app->singleton('resource-wizard', static fn () => app(ResourceWizard::class));

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

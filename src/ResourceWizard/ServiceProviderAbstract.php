<?php

namespace Jeovajr\ResourceWizard;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Jeovajr\ResourceWizard\Console\Build;
use Jeovajr\ResourceWizard\Console\Create;
use Jeovajr\ResourceWizard\Events\EventAdd;
use Jeovajr\ResourceWizard\Events\EventBrowse;
use Jeovajr\ResourceWizard\Events\EventDelete;
use Jeovajr\ResourceWizard\Events\EventEdit;
use Jeovajr\ResourceWizard\Events\EventLock;
use Jeovajr\ResourceWizard\Events\EventRead;
use Jeovajr\ResourceWizard\Events\EventUnlock;
use Jeovajr\ResourceWizard\Facades\Wizard;
use Jeovajr\ResourceWizard\Models\ResourceModel;
use Jeovajr\ResourceWizard\Requests\FormRequest;
use Jeovajr\ResourceWizard\Services\Wizard as WizardService;

class ServiceProviderAbstract extends ServiceProvider implements DeferrableProvider
{
    final public function boot(
        Router $router,
        AuthManager $auth,
        Gate $gate,
    ): self {
        $this->mergeConfigFrom(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'config', 'resource-wizard.php']), 'resource-wizard');
        $this->publishes([implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'config', 'resource-wizard.php']) => config_path('resource-wizard.php')], 'resource-wizard');

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
            WizardService::class,
            Wizard::class,
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

        $this->app->singleton(Wizard::class, fn () => new WizardService());

        $this->app->singleton('wizard', static fn () => app(Wizard::class));

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

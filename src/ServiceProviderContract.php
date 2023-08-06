<?php

declare(strict_types=1);

namespace ResourceWizard;

use Illuminate\Routing\Router;

interface ServiceProviderContract
{
    /**
     * Register the Auth0 guards.
     */
    public function registerGuards(): void;

    /**
     * Register the service middleware.
     */
    public function registerMiddleware(
        Router $router,
    ): void;

    /**
     * Register the authentication routes.
     */
    public function registerRoutes(): void;
}

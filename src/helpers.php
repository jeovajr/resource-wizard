<?php

use ResourceWizard\Services\ResourceWizard;

if (! function_exists('wizard')) {
    /**
     * Access the container resource wizard.
     */
    function wizard(): ResourceWizard
    {
        return app('resource-wizard');
    }
}

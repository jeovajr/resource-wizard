<?php

use ResourceWizard\Services\Wizard;

if (! function_exists('wizard')) {
    /**
     * Access the container resource wizard.
     */
    function wizard(): Wizard
    {
        return app('wizard');
    }
}

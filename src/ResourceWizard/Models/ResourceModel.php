<?php

namespace Jeovajr\ResourceWizard\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ResourceModel
{
    /**
     * List of relations returned by the model, to be used by the controller.
     * Use dot syntax for multi level relations.
     */
    public static function getBrowseRelations(): array;

    /**
     * List of relations returned by the model, to be used by the controller.
     * Use dot syntax for multi level relations.
     */
    public static function getReadRelations(): array;

    /**
     * Get the searchable column names
     *
     * @return string[]
     */
    public static function getSearchableColumns(): array;

    /**
     * Get the user that created the DummyStudlyS.
     */
    public function createdByUser(): BelongsTo;

    /**
     * Get the user that modified the DummyStudlyS.
     */
    public function modifiedByUser(): BelongsTo;

    /**
     * Get the user that locked the DummyStudlyS.
     */
    public function lockedByUser(): BelongsTo;
}

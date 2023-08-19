<?php

declare(strict_types=1);

namespace Jeovajr\ResourceWizard\Events;

/**
 * EventEdit general class.
 * Broadcast events via broadcast driver.
 *
 * @author        Jeova Goncalves <jeova.goncalves1@gmail.com>
 * @copyright (c) 2023, Jeova Goncalves.
 */
abstract class EventEdit extends Event
{
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return parent::broadcastAs().'.edited';
    }
}

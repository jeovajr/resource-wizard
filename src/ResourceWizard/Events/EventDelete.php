<?php

declare(strict_types=1);

namespace Jeovajr\ResourceWizard\Events;

/**
 * EventDelete general class.
 * Broadcast events via broadcast driver.
 *
 * @author        Jeova Goncalves <jeova.goncalves1@gmail.com>
 * @copyright (c) 2023, Jeova Goncalves.
 */
abstract class EventDelete extends Event
{
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return parent::broadcastAs().'.deleted';
    }
}

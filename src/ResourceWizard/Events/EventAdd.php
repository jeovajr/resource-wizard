<?php

declare(strict_types=1);

namespace Jeovajr\ResourceWizard\Events;

use Illuminate\Broadcasting\PresenceChannel;

/**
 * EventAdd general class.
 * Broadcast events via broadcast driver.
 *
 * @author        Jeova Goncalves <jeova.goncalves1@gmail.com>
 * @copyright (c) 2023, Jeova Goncalves.
 */
abstract class EventAdd extends Event
{
    /**
     * Get the channels array.
     *
     * @return PresenceChannel[]
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('wizard.resource.'.parent::getName().'.m'),
            new PresenceChannel('wizard.resource.'.parent::getName().'.b'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return parent::getName().'.added';
    }
}

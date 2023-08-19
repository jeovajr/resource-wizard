<?php

declare(strict_types=1);

namespace Jeovajr\ResourceWizard\Events;

use Illuminate\Broadcasting\PresenceChannel;

/**
 * EventBrowse general class.
 * Broadcast events via broadcast driver.
 *
 * @author        Jeova Goncalves <jeova.goncalves1@gmail.com>
 * @copyright (c) 2023, Jeova Goncalves.
 */
abstract class EventBrowse extends Event
{
    use Requester;

    /**
     * Create a new event instance.
     *
     * @param  array{id: int|string, name: string}  $requester
     * @return void
     */
    public function __construct(string $name, array $requester)
    {
        parent::__construct($name);
        $this->requester = $requester;
    }

    /**
     * Get the channels array.
     *
     * @return PresenceChannel[]
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('wizard.resource.'.parent::getName().'.b'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return parent::getName().'.browsed';
    }
}

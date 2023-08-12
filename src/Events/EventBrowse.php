<?php

declare(strict_types=1);

namespace ResourceWizard\Events;

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
     * @param  string  $account The event account
     * @return void
     */
    public function __construct(string $name, array $requester, string $account)
    {
        parent::__construct($name, null, $account);
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
            new PresenceChannel(parent::getAccount().'.resource.'.parent::getName().'.b'),
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

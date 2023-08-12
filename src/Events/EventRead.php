<?php

declare(strict_types=1);

namespace ResourceWizard\Events;

use Illuminate\Broadcasting\PresenceChannel;

/**
 * EventRead general class.
 * Broadcast events via broadcast driver.
 *
 * @author        Jeova Goncalves <jeova.goncalves1@gmail.com>
 * @copyright (c) 2023, Jeova Goncalves.
 */
abstract class EventRead extends Event
{
    use Requester;

    /**
     * Create a new event instance.
     *
     * @param  string  $account The event account
     * @param  array{id: int|string, name: string}  $requester The user that requested the resource
     */
    public function __construct(string $name, mixed $object, string $account, array $requester)
    {
        parent::__construct($name, $object, $account);
        $this->requester = $requester;
    }

    /**
     * Get the channels array.
     *
     * @return PresenceChannel[]
     */
    public function broadcastOn(): array
    {
        if (is_object($this->object) && isset($this->object->id)) {
            return [
                new PresenceChannel(parent::getAccount().'.resource.'.parent::getName().'.r'.$this->object->id),
            ];
        }

        return [];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return parent::getName().'.read';
    }
}
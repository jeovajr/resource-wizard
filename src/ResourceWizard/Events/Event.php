<?php

declare(strict_types=1);

namespace ResourceWizard\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event global class.
 *
 * @author        Jeova Goncalves <jeova.goncalves1@gmail.com>
 * @copyright (c) 2023, Jeova Goncalves.
 */
abstract class Event implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public bool $afterCommit = true;

    public mixed $object;

    private string $name;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $object
     */
    public function __construct(string $name, mixed $object = null)
    {
        $this->name = $name;
        $this->object = $object;
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
                new PresenceChannel('wizard.resource.'.$this->name.'.m'),
                new PresenceChannel('wizard.resource.'.$this->name.'.b'),
                new PresenceChannel('wizard.resource.'.$this->name.'.r'.$this->object->id),
                new PresenceChannel('wizard.resource.'.$this->name.'.e'.$this->object->id),
                new PresenceChannel('wizard.resource.'.$this->name.'.d'.$this->object->id),
            ];
        }

        return [
            new PresenceChannel('.resource.'.$this->name.'.m'),
            new PresenceChannel('.resource.'.$this->name.'.b'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return $this->name;
    }

    /**
     * Get the resource name
     */
    public function getName(): string
    {
        return $this->name;
    }
}

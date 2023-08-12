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

    private string $account;

    private string $name;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $object
     * @param  string  $account The event account
     */
    public function __construct(string $name, mixed $object = null, string $account = 'default')
    {
        $this->name = $name;
        $this->object = $object;
        $this->account = $account;
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
                new PresenceChannel($this->account.'.resource.'.$this->name.'.m'),
                new PresenceChannel($this->account.'.resource.'.$this->name.'.b'),
                new PresenceChannel($this->account.'.resource.'.$this->name.'.r'.$this->object->id),
                new PresenceChannel($this->account.'.resource.'.$this->name.'.e'.$this->object->id),
                new PresenceChannel($this->account.'.resource.'.$this->name.'.d'.$this->object->id),
            ];
        }

        return [
            new PresenceChannel($this->account.'.resource.'.$this->name.'.m'),
            new PresenceChannel($this->account.'.resource.'.$this->name.'.b'),
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

    /**
     * Get the account
     */
    public function getAccount(): string
    {
        return $this->account;
    }
}

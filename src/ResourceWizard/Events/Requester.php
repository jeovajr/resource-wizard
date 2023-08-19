<?php

declare(strict_types=1);

namespace ResourceWizard\Events;

trait Requester
{
    /**
     * @var array{id: int|string, name: string} The requester user
     */
    public array $requester = [
        'id' => 0,
        'name' => 'System',
    ];

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'requester' => [
                'id' => $this->requester['id'],
                'name' => $this->requester['name'],
            ],
        ];
    }
}

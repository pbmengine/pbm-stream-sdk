<?php

declare(strict_types=1);

namespace Pbmengine\Stream\Concerns;

use Illuminate\Http\Client\Response;

trait HandleEvents
{
    /**
     * @param array<string, mixed> $input
     */
    public function record(array $input): Response
    {
        return $this
            ->client()
            ->post($this->path('projects', $this->project, 'collections', $this->getCollection(), 'events'), $input);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function updateEvent(string $eventId, array $input): Response
    {
        return $this
            ->client()
            ->put($this->path('projects', $this->project, 'collections', $this->getCollection(), 'events', $eventId), $input);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function updateEvents(string $field, string $value, array $input): Response
    {
        return $this
            ->client()
            ->put($this->path('projects', $this->project, 'collections', $this->getCollection(), 'events', 'condition', $field, $value), $input);
    }

    /**
     * @param array<mixed> $conditions
     * @param array<string, mixed> $input
     */
    public function updateEventsWithConditions(array $conditions, array $input): Response
    {
        return $this
            ->client()
            ->put($this->path('projects', $this->project, 'collections', $this->getCollection(), 'events'), [
                'conditions' => $conditions,
                'params' => $input]
            );
    }

    public function deleteEvent(string $eventId): Response
    {
        return $this
            ->client()
            ->delete($this->path('projects', $this->project, 'collections', $this->getCollection(), 'events', $eventId));
    }
}

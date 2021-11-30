<?php

namespace Pbmengine\Stream\Concerns;

use Illuminate\Http\Client\Response;

trait HandleEvents
{
    public function record(array $input): Response
    {
        return $this
            ->client()
            ->post("/projects/{$this->project}/collections/{$this->collection}/events", $input);
    }

    public function updateEvent(string $eventId, array $input): Response
    {
        return $this
            ->client()
            ->put("/projects/{$this->project}/collections/{$this->collection}/events/{$eventId}", $input);
    }

    public function deleteEvent(string $eventId): Response
    {
        return $this
            ->client()
            ->delete("/projects/{$this->project}/collections/{$this->collection}/events/{$eventId}");
    }

    public function query(): Response
    {
        return $this
            ->client()
            ->post("/projects/{$this->project}/collections/{$this->collection}/query");
    }
}

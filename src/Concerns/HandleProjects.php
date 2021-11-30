<?php

namespace Pbmengine\Stream\Concerns;

use Illuminate\Http\Client\Response;

trait HandleProjects
{
    public function project(): Response
    {
        return $this
            ->client()
            ->get("/projects/{$this->project}");
    }

    public function collections(): Response
    {
        return $this
            ->client()
            ->get("/projects/{$this->project}/collections");
    }

    public function collection(): Response
    {
        return $this
            ->client()
            ->get("/projects/{$this->project}/collections/{$this->collection}");
    }

    public function createIndex(string $field): Response
    {
        return $this
            ->client()
            ->post("/projects/{$this->project}/collections/{$this->collection}/indexes/{$field}");
    }

    public function dropIndex(string $field): Response
    {
        return $this
            ->client()
            ->delete("/projects/{$this->project}/collections/{$this->collection}/indexes/{$field}");
    }

    public function validateEvent(array $event): Response
    {
        return $this
            ->client()
            ->post("/projects/{$this->project}/collections/{$this->collection}/validation");
    }
}

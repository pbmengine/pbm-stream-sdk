<?php

declare(strict_types=1);

namespace Pbmengine\Stream\Concerns;

use Illuminate\Http\Client\Response;

trait HandleProjects
{
    public function project(): Response
    {
        return $this
            ->client()
            ->get($this->path('projects', $this->project));
    }

    public function collections(): Response
    {
        return $this
            ->client()
            ->get($this->path('projects', $this->project, 'collections'));
    }

    public function collection(): Response
    {
        return $this
            ->client()
            ->get($this->path('projects', $this->project, 'collections', $this->getCollection()));
    }

    public function addPropertyKey(string $field, mixed $defaultValue, string $propertyType): Response
    {
        $data = [
            'key' => $field,
            'default_value' => $defaultValue,
            'property_type' => $propertyType,
        ];

        return $this
            ->client()
            ->post($this->path('projects', $this->project, 'collections', $this->getCollection(), 'properties'), $data);
    }

    public function deletePropertyKey(string $field): Response
    {
        $data = [
            'key' => $field,
        ];

        return $this
            ->client()
            ->delete($this->path('projects', $this->project, 'collections', $this->getCollection(), 'properties'), $data);
    }

    public function renamePropertyKey(string $oldField, string $newField): Response
    {
        $data = [
            'old_key' => $oldField,
            'new_key' => $newField,
        ];

        return $this
            ->client()
            ->put($this->path('projects', $this->project, 'collections', $this->getCollection(), 'properties'), $data);
    }

    public function createIndex(string $field): Response
    {
        return $this
            ->client()
            ->post($this->path('projects', $this->project, 'collections', $this->getCollection(), 'indexes', $field));
    }

    public function dropIndex(string $field): Response
    {
        return $this
            ->client()
            ->delete($this->path('projects', $this->project, 'collections', $this->getCollection(), 'indexes', $field));
    }

    /**
     * @param array<string, mixed> $event
     */
    public function validateEvent(array $event): Response
    {
        return $this
            ->client()
            ->post($this->path('projects', $this->project, 'collections', $this->getCollection(), 'validation'), $event);
    }
}

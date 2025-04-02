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

    /**
     * @param $field
     * @param $defaultValue
     * @param $propertyType
     * @return Response
     */
    public function addPropertyKey($field, $defaultValue, $propertyType): Response
    {
        $data = [
            'key' => $field,
            'default_value' => $defaultValue,
            'property_type' => $propertyType,
        ];

        return $this
            ->client()
            ->post("/projects/{$this->project}/collections/{$this->collection}/properties", $data);
    }

    /**
     * @param string $field
     * @return Response
     */
    public function deletePropertyKey($field): Response
    {
        $data = [
            'key' => $field,
        ];

        return $this
            ->client()
            ->delete("/projects/{$this->project}/collections/{$this->collection}/properties", $data);
    }

    /**
     * @param string $oldField
     * @param string $newField
     * @return Response
     */
    public function renamePropertyKey($oldField, $newField): Response
    {
        $data = [
            'old_key' => $oldField,
            'new_key' => $newField,
        ];

        return $this
            ->client()
            ->put("/projects/{$this->project}/collections/{$this->collection}/properties", $data);
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
            ->post("/projects/{$this->project}/collections/{$this->collection}/validation", $event);
    }
}

<?php

namespace Pbmengine\Stream;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Pbmengine\Stream\Concerns\HandleEvents;
use Pbmengine\Stream\Concerns\HandleProjects;
use Pbmengine\Stream\Concerns\HandleQueries;
use Pbmengine\Stream\Concerns\HandleStatus;
use Pbmengine\Stream\Concerns\HandleTests;

class Stream
{
    use HandleEvents;
    use HandleProjects;
    use HandleStatus;
    use HandleTests;
    use HandleQueries;

    protected ?string $collection = null;
    protected int $timeout = 45;
    protected string $url;
    protected string $project;
    protected string $accessKey;

    public function __construct($url, $project, $accessKey)
    {
        $this->url = $url;
        $this->project = $project;
        $this->accessKey = $accessKey;
    }

    public function setProject(string $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getProject(): string
    {
        return $this->project;
    }

    public function setAccessKey(string $accessKey): self
    {
        $this->accessKey = $accessKey;

        return $this;
    }

    public function setCollection(string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function getCollection(): string
    {
        return $this->collection;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $this->accessKey,
            'Accept' => 'application/json'
        ])
            ->timeout($this->timeout)
            ->baseUrl($this->url);
    }
}

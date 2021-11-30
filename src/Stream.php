<?php

namespace Pbmengine\Stream;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Pbmengine\Stream\Concerns\HandleEvents;
use Pbmengine\Stream\Concerns\HandleProjects;
use Pbmengine\Stream\Concerns\HandleStatus;
use Pbmengine\Stream\Concerns\HandleTests;

class Stream
{
    use HandleEvents;
    use HandleProjects;
    use HandleStatus;
    use HandleTests;

    protected ?string $collection = null;
    protected int $timeout = 5;

    public function __construct(
        protected string $url,
        protected string $project,
        protected string $accessKey
    ) {
    }

    public function setProject(string $project): self
    {
        $this->project = $project;

        return $this;
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

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $this->accessKey,
            'Accept' => 'application/json'
        ])
            ->timeout($this->timeout)
            ->baseUrl($this->url);
    }
}

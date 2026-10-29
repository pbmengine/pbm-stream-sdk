<?php

declare(strict_types=1);

namespace Pbmengine\Stream;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use LogicException;
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

    public function __construct(string $url, string $project, string $accessKey)
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
        if ($this->collection === null || $this->collection === '') {
            throw new LogicException('No collection set. Call setCollection() or use stream(\'<collection>\') first.');
        }

        return $this->collection;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Build a request path from raw segments. Each segment is percent-encoded so
     * caller-supplied values (ids, field names, ...) cannot alter the request
     * target, e.g. via "../", "?" or "#".
     */
    public function path(string ...$segments): string
    {
        return '/'.implode('/', array_map(rawurlencode(...), $segments));
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

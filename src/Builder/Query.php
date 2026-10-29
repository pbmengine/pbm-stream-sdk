<?php

declare(strict_types=1);

namespace Pbmengine\Stream\Builder;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Pbmengine\Stream\Stream;

class Query
{
    protected Stream $stream;

    /**
     * The where constraints for the query.
     *
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $wheres = null;

    /**
     * The columns that should be returned.
     *
     * @var array<int, string>
     */
    protected array $columns = ['*'];

    /**
     * Indicates if the query returns distinct results.
     * Occasionally contains the columns that should be distinct.
     */
    protected bool $distinct = false;

    /**
     * Aggregate function and column to be run
     *
     * @var array<string, mixed>|null
     */
    protected ?array $aggregate = null;

    /**
     * The groupings for the query.
     *
     * @var array<int, mixed>|null
     */
    protected ?array $groups = null;

    /**
     * The orderings for the query.
     *
     * @var array<int, array{column: string, direction: string}>|null
     */
    protected ?array $orders = null;

    /**
     * Should the query be paginated.
     */
    protected bool $pagination = false;

    /**
     * The maximum number of records to return.
     */
    protected int $limit = 0;

    /**
     * The number of records to skip.
     */
    protected int $offset = 0;

    /**
     * The timeframe for the query
     * startDateTime, endDateTime ISO8601 String
     *
     * @var array<int, string>|null
     */
    protected ?array $timeFrame = null;

    /**
     * interval for the query
     */
    protected ?string $interval = null;

    /**
     * pipeline as array see aggregation pipelines
     *
     * @var array<mixed>|null
     */
    protected ?array $pipeline = null;

    /**
     * All of the available clause operators.
     *
     * @var array<int, string>
     */
    public array $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'not like', 'between', 'ilike', '&',
        '|', '^', '<<', '>>', 'rlike', 'regexp', 'not regexp',
        'exists', 'type', 'mod', 'where', 'all', 'size', 'regex', 'text', 'slice', 'elemmatch', 'geowithin',
        'geointersects', 'near', 'nearsphere', 'geometry',
        'maxdistance', 'center', 'centersphere',
        'box', 'polygon', 'uniquedocs',
    ];

    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    public function groupBy(mixed ...$groups): self
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array)$this->groups,
                Arr::wrap($group)
            );
        }

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc'): self
    {
        $direction = strtolower($direction);

        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * @param string $column
     * @return $this
     */
    public function orderByDesc($column): self
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * @param int $value
     * @return $this
     */
    public function take($value): self
    {
        return $this->limit($value);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function limit($value): self
    {
        $this->limit = (int)$value;

        return $this;
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return $this
     */
    public function forPage($page, $perPage = 15): self
    {
        return $this->offset(((int)$page - 1) * $perPage)->limit($perPage);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function offset($value): self
    {
        $this->offset = max(0, (int)$value);

        return $this;
    }

    /**
     * @param array<int, string> $columns
     * @return $this
     */
    public function select($columns = ['*']): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param mixed $start
     * @param mixed $end
     * @return $this
     */
    public function timeFrame($start, $end): self
    {
        if (is_string($start) && TimeFrame::inKeys($start) && is_int($end)) {
            $this->timeFrame = TimeFrame::byKey($start, (int)$end);

            return $this;
        }

        if ($start instanceof CarbonInterface && $end instanceof CarbonInterface) {
            if (TimeFrame::isStartGreaterThanEndDate($start, $end)) {
                throw new \InvalidArgumentException('start date can not be greater or equal as end date');
            }

            $this->timeFrame = [
                $start->toIso8601String(),
                $end->toIso8601String()
            ];

            return $this;
        }

        // here we check if either start or end is carbon instance
        $start = $start instanceof CarbonInterface
            ? $start
            : Carbon::parse($start);

        $end = $end instanceof CarbonInterface
            ? $end
            : Carbon::parse($end);

        if (TimeFrame::isStartGreaterThanEndDate($start, $end)) {
            throw new \InvalidArgumentException('start date can not be greater or equal as end date');
        }

        $this->timeFrame = [$start->toIso8601String(), $end->toIso8601String()];

        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator, $value, $boolean = 'and'): self
    {
        $type = 'basic';

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere($column, $operator, $value): self
    {
        $this->where($column, $operator, $value, 'or');

        return $this;
    }

    /**
     * @param string $column
     * @param mixed $values
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false): self
    {
        $type = $not ? 'NotIn' : 'In';

        // Next, if the value is Arrayable we need to cast it to its raw array form so we
        // have the underlying array value instead of an Arrayable object which is not
        // able to be added as a binding, etc. We will then add to the wheres array.
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        return $this;
    }

    /**
     * @param string $column
     * @param mixed $values
     * @return $this
     */
    public function orWhereIn($column, $values): self
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * @param string $column
     * @param mixed $values
     * @param string $boolean
     * @return $this
     */
    public function whereNotIn($column, $values, $boolean = 'and'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * @param string $column
     * @param mixed $values
     * @return $this
     */
    public function orWhereNotIn($column, $values): self
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * @param string $function
     * @param array<int, mixed> $columns
     * @return $this
     */
    protected function setAggregate($function, $columns): self
    {
        $this->aggregate = compact('function', 'columns');

        return $this;
    }

    /**
     * @param string $columns
     */
    public function count($columns = '*'): Response
    {
        return $this->setAggregate(__FUNCTION__, [$columns])->get();
    }

    /**
     * @param string|false $column
     * @return $this
     */
    public function distinct($column = false): self
    {
        $this->distinct = true;

        if ($column) {
            $this->columns = [$column];
        }

        return $this;
    }

    /**
     * @param array<mixed> $pipeline
     * @return Response
     */
    public function aggregate($pipeline): Response
    {
        $this->pipeline = $pipeline;

        return $this->get();
    }

    /**
     * @param string $key
     * @return Response
     */
    public function countUnique($key): Response
    {
        $this->distinct($key);

        return $this->count($key);
    }

    /**
     * @param string $column
     * @return Response
     */
    public function sum($column): Response
    {
        return $this->setAggregate(__FUNCTION__, [$column])->get();
    }

    /**
     * @param string $column
     * @return Response
     */
    public function avg($column): Response
    {
        return $this->setAggregate(__FUNCTION__, [$column])->get();
    }

    /**
     * @param string $column
     * @return Response
     */
    public function max($column): Response
    {
        return $this->setAggregate(__FUNCTION__, [$column])->get();
    }

    /**
     * @param string $column
     * @return Response
     */
    public function min($column): Response
    {
        return $this->setAggregate(__FUNCTION__, [$column])->get();
    }

    public function get(): Response
    {
        $this->pagination = false;

        return $this->request();
    }

    /**
     * @param int $perPage
     * @param int $page
     * @return Response
     */
    public function paginate($perPage = 15, $page = 1): Response
    {
        $this->pagination = true;

        $this->forPage($page, $perPage);

        return $this->request();
    }

    protected function request(): Response
    {
        return $this->stream
            ->client()
            ->post(
                $this->stream->path('projects', $this->stream->getProject(), 'collections', $this->stream->getCollection(), 'query'),
                $this->explain()
            );
    }

    public function encodedQuery(): string
    {
        return base64_encode(json_encode($this->explain(), JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<string, mixed>
     */
    public function explain(): array
    {
        return [
            'wheres' => $this->wheres,
            'orders' => $this->orders,
            'groups' => $this->groups,
            'columns' => $this->columns,
            'distinct' => $this->distinct,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'pagination' => $this->pagination,
            'aggregate' => $this->aggregate,
            'timeFrame' => $this->timeFrame,
            'interval' => $this->interval,
            'pipeline' => $this->pipeline,
        ];
    }
}

<?php

namespace Pbmengine\Stream\Builder;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Pbmengine\Stream\Stream;

class Query
{
    /**
     * The where constraints for the query.
     */
    protected ?array $wheres = null;

    /**
     * The columns that should be returned.
     */
    protected array $columns = ['*'];

    /**
     * Indicates if the query returns distinct results.
     * Occasionally contains the columns that should be distinct.
     */
    protected ?array $distinct = null;

    /**
     * Aggregate function and column to be run
     */
    protected ?array $aggregate = null;

    /**
     * The groupings for the query.
     */
    protected ?array $groups = null;

    /**
     * The orderings for the query.
     */
    protected ?array $orders = null;

    /**
     * The maximum number of records to return.
     */
    protected int $limit = 15;

    /**
     * The number of records to skip.
     */
    protected int $offset = 0;

    /**
     * The timeframe for the query
     * startDateTime, endDateTime ISO8601 String
     */
    protected ?array $timeFrame = null;

    /**
     * interval for the query
     */
    protected ?string $interval = null;

    /**
     * All of the available clause operators.
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

    public function __construct(protected Stream $stream)
    {}

    public function groupBy(string|array ...$groups): self
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array)$this->groups,
                Arr::wrap($group)
            );
        }

        return $this;
    }

    public function orderBy(string $column, $direction = 'asc'): self
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

    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'desc');
    }

    public function take(int $value): self
    {
        return $this->limit($value);
    }

    public function limit(int $value): self
    {
        $this->limit = (int)$value;

        return $this;
    }

    public function forPage(int $page, $perPage = 15): self
    {
        return $this->offset(((int)$page - 1) * $perPage)->limit($perPage);
    }

    public function offset(int $value): self
    {
        $this->offset = max(0, (int)$value);

        return $this;
    }

    public function interval(string $interval): self
    {
        if (!Interval::exists($interval)) {
            throw new \InvalidArgumentException('interval ' . $interval . ' does not exist');
        }

        $this->interval = $interval;

        return $this;
    }

    public function select(array $columns = ['*']): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function timeFrame(mixed $start, mixed $end): self
    {
        if (TimeFrame::inKeys($start) && is_int($end)) {
            $this->timeFrame = TimeFrame::byKey($start, (int)$end);

            return $this;
        }

        if ($start instanceof Carbon && $end instanceof Carbon) {
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
        $start = $start instanceof Carbon
            ? $start
            : Carbon::parse($start);

        $end = $end instanceof Carbon
            ? $end
            : Carbon::parse($end);

        if (TimeFrame::isStartGreaterThanEndDate($start, $end)) {
            throw new \InvalidArgumentException('start date can not be greater or equal as end date');
        }

        $this->timeFrame = [$start->toIso8601String(), $end->toIso8601String()];

        return $this;
    }

    public function where(string $column, string $operator, mixed $value, string $boolean = 'and'): self
    {
        $type = 'basic';

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $this->where($column, $operator, $value, 'or');

        return $this;
    }

    public function whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false): self
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

    public function orWhereIn(string $column, mixed $values): self
    {
        return $this->whereIn($column, $values, 'or');
    }

    public function whereNotIn(string $column, mixed $values, string $boolean = 'and'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    public function orWhereNotIn(string $column, mixed $values): self
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    protected function setAggregate(string $function, array $columns): self
    {
        $this->aggregate = compact('function', 'columns');

        return $this;
    }

    public function count($columns = '*'): Response
    {
        return $this->setAggregate(__FUNCTION__, [$columns])->get();
    }

    public function distinct(array $columns): self
    {
        $this->distinct = $columns;

        return $this;
    }

    public function countUnique(string $key): Response
    {
        $this->distinct = [$key];

        return $this->count($key);
    }

    public function sum(string $column): Response
    {
        return $this->setAggregate(__FUNCTION__, [$column])->get();
    }

    public function avg(string $column): Response
    {
        return $this->setAggregate(__FUNCTION__, [$column])->get();
    }

    public function max(string $column): Response
    {
        return $this->setAggregate(__FUNCTION__, [$column])->get();
    }

    public function min(string $column)
    {
        return $this->setAggregate(__FUNCTION__, [$column])->get();
    }

    public function get(): Response
    {
        return $this->stream
            ->client()
            ->post(
                "/projects/{$this->stream->getProject()}/collections/{$this->stream->getCollection()}/query",
                $this->explain()
            );
    }

    public function encodedQuery(): string
    {
        return base64_encode(json_encode($this->explain()));
    }

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
            'aggregate' => $this->aggregate,
            'timeFrame' => $this->timeFrame,
            'interval' => $this->interval,
        ];
    }
}

<?php

namespace Pbmengine\Stream\Builder;

use Carbon\Carbon;
use Illuminate\Support\Str;

class TimeFrame
{
    const THIS_MINUTES = 'this_minutes_';
    const THIS_HOURS = 'this_hours_';
    const THIS_DAYS = 'this_days_';
    const THIS_WEEKS = 'this_weeks_';
    const THIS_MONTHS = 'this_months_';
    const THIS_YEARS = 'this_years_';

    const PREV_MINUTES = 'previous_minutes_';
    const PREV_HOURS = 'previous_hours_';
    const PREV_DAYS = 'previous_days_';
    const PREV_WEEKS = 'previous_weeks_';
    const PREV_MONTHS = 'previous_months_';
    const PREV_YEARS = 'previous_years_';

    const KEYS = [
        self::THIS_MINUTES => 'minute',
        self::THIS_HOURS => 'hour',
        self::THIS_DAYS => 'day',
        self::THIS_WEEKS => 'week',
        self::THIS_MONTHS => 'month',
        self::THIS_YEARS => 'year',
        self::PREV_MINUTES => 'minute',
        self::PREV_HOURS => 'hour',
        self::PREV_DAYS => 'day',
        self::PREV_WEEKS => 'week',
        self::PREV_MONTHS => 'month',
        self::PREV_YEARS => 'year',
    ];

    public static function inKeys(string $value): bool
    {
        return array_key_exists($value, self::KEYS);
    }

    public static function isStartGreaterThanEndDate(Carbon $start, Carbon $end): bool
    {
        return $start->greaterThanOrEqualTo($end);
    }

    public static function byKey(string $key, int $value): array
    {
        $now = now();
        $nowCloned = clone $now;

        if (self::includeThis($key)) {
            return [
                self::getStartDateOfUnit($now, self::getUnit($key), $value)->toIso8601String(),
                $nowCloned->toIso8601String(),
            ];
        }

        if (self::includePrevious($key)) {
            return [
                self::getStartDateOfUnit($now, self::getUnit($key), $value)->toIso8601String(),
                self::getEndDateOfUnit($nowCloned, self::getUnit($key), $value)->toIso8601String(),
            ];
        }

        throw new \InvalidArgumentException('missing arguments');
    }

    protected static function getStartDateOfUnit(Carbon $date, string $unit, int $value): Carbon
    {
        switch($unit) {
            case('minute'):
                return $date->subMinutes($value)->startOfMinute();
            case('hour'):
                return $date->subHours($value)->startOfHour();
            case('day'):
                return $date->subDays($value)->startOfDay();
            case('week'):
                return $date->subWeeks($value)->startOfWeek();
            case('month'):
                return $date->subMonths($value)->startOfMonth();
            case('year'):
                return $date->subYears($value)->startOfYear();
            default:
                return now();
        }
    }

    protected static function getEndDateOfUnit(Carbon $date, string $unit, int $value): Carbon
    {
        switch($unit) {
            case('minute'):
                return $date->subMinutes(1)->endOfMinute();
            case('hour'):
                return $date->subHours(1)->endOfHour();
            case('day'):
                return $date->subDays(1)->endOfDay();
            case('week'):
                return $date->subWeeks(1)->endOfWeek();
            case('month'):
                return $date->subMonths(1)->endOfMonth();
            case('year'):
                return $date->subYears(1)->endOfYear();
            default:
                return now();
        }
    }

    protected static function getUnit(string $key): string
    {
        return self::KEYS[$key];
    }

    protected static function includeThis($key): bool
    {
        return Str::contains($key, 'this');
    }

    protected static function includePrevious($key): bool
    {
        return Str::contains($key, 'previous');
    }
}

<?php

namespace Pbmengine\Stream\Builder;

class Interval
{
    const MINUTELY = 'minutely';
    const HOURLY = 'hourly';
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';

    const KEYS = [
        self::MINUTELY,
        self::HOURLY,
        self::DAILY,
        self::WEEKLY,
        self::MONTHLY,
        self::YEARLY,
    ];

    public static function exists(string $key): bool
    {
        return in_array($key, self::KEYS);
    }
}

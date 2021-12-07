This PHP package skeleton is inspired by the awesome [Spatie Package](https://github.com/spatie/skeleton-php) 

# PBM Stream API SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gemzio/pbm-stream-sdk.svg?style=flat-square)](https://packagist.org/packages/pbmengine/pbm-stream-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/gemzio/pbm-stream-sdk.svg?style=flat-square)](https://packagist.org/packages/pbmengine/pbm-stream-sdk)

## Installation

You can install the package via composer:

```bash
composer require pbmengine/pbm-stream-sdk
```

## Usage

````shell
php artisan vendor:publish
````

Update the config file with url, project id and access key.

```php
# in ./config/pbm-stream.php
return [
    'url' => env('PBM_STREAM_URL', ''),
    'project' => env('PBM_STREAM_PROJECT', ''),
    'access_key' => env('PBM_STREAM_ACCESS_KEY', '')
];
```

Now you can use the stream api.

```php
# via helper
stream('logins')->record(['user' => 1, 'age' => 30]);

# via Facade
\Pbmengine\Stream\Facades\Stream::collection('logins')->record(['user' => 1, 'age' => 30]);
```

Available methods
```php
# record event
stream('logins')->record(['user' => 1, 'age' => 30]);

# update event
stream('logins')->updateEvent('<event id>', ['age' => 30]);

# delete event
stream('logins')->deleteEvent('<event id>');

# get project information
stream()->project();

# get project collections
stream()->collections();

# get collection informations
stream('logins')->collection();

# validate collection event
stream('logins')->validateEvent(['user' => 2, 'age' => 10]);

# create collection index
stream('logins')->createIndex('<field>');

# drop collection index
stream('logins')->dropIndex('<field>');

# test event to check if it's a valid event
# you do not need any collection for this request
stream()->testEvent(['user' => 2, 'age' => 10]);

# queries
$response = stream('logins')
    ->query()
        ->where('a', '=', 2)
        ->whereIn('field', ['array', '...']) 
        ->orWhere('b', '>', 6)
        ->timeFrame('start', 'end')
        ->timeFrame(TimeFrame::THIS_DAYS, 5)
        ->groupBy('field') // (['field a', 'field b'])
        ->orderBy('field') // -field or ['field a', '-field b']
        ->count(); // sum(field) | avg(field) | max(field) | min(field) | countUnique(field) | selectUnique(field)

// get events with pagination
$response = stream('pages')
    ->query()
    ->where('event', '=', 'page.viewed')
    ->orderByDesc('timestamp')
    ->forPage(3, 30)
    ->get();

# complex queries
# for more complex queries use the aggregate function
stream('pages')
    ->query()
    ->aggregate([
        ['$match' => ['event' => 'pageViewed']]
    ]);

```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

### Security

If you discover any security related issues, please email stefan@sriehl.com instead of using the issue tracker.

## Credits

- [Stefan Riehl](https://github.com/stefanriehl)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

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

# update several events with condition
# field is the condition field with value
# e.g. field is userId and value is 300
# stream('logins')->updateEvents('userId', 300, ['age' => 30]);
# means: update all events where userId is 300 and set age to 30
stream('logins')->updateEvents('field', 'value', ['age' => 30]);

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

# test event to check if it's a valid event for any collection
# you do not need any collection for this request
stream()->testEvent(['user' => 2, 'age' => 10]);

# create collection index
# timestamp and _id are automatically indexed
stream('logins')->createIndex('<field>');

# drop collection index
stream('logins')->dropIndex('<field>');

# query options
$response = stream('logins')
    ->query()
    ->select(['_id', 'event', 'itemPrice'])
    ->where('a', '=', 2)
    ->whereIn('<column>', ['array', '...']) 
    ->orWhere('b', '>', 6)
    ->timeFrame('<start date iso 8601>', '<end date iso 8601>')
    ->timeFrame(TimeFrame::THIS_DAYS, 5)
    ->timeFrame(TimeFrame::PREVIOUS_DAYS, 6)
    ->groupBy('<column>') // (['field a', 'field b'])
    ->orderBy('<column>', 'asc') // second parameter is optional, default is asc 
    ->orderByDesc('<column>') // order desc
    ->count(); // sum(field) | avg(field) | max(field) | min(field) | countUnique(field) | selectUnique(field)

// get events
$response = stream('pages')
    ->query()
    ->where('event', '=', 'page.viewed')
    ->orWhere('event', '=', 'login.viewed')
    ->take(10)
    ->orderByDesc('timestamp')
    ->get();

// get events with pagination
$response = stream('pages')
    ->query()
    ->where('event', '=', 'page.viewed')
    ->orderByDesc('timestamp')
    ->paginate(10, 1); // per page 10 events on page 1

# complex queries
# for more complex queries use the aggregate function
stream('pages')
    ->query()
    ->aggregate([
        ['$match' => ['event' => 'pageViewed']]
    ]);

```

Responses

Responses are always Laravel Http Client Responses!

```php
# get() method
$response = stream('pages')->take(1)->get()->json();
```
```json
{
  "data": [
        {
            "_id": "61b28877a57f17655163cea2",
            "event": "video.started",
            "userId": 107,
            "customerId": 772,
            "hasContract": false,
            "customerAge": 34,
            "hasChildren": false,
            "persona": "mf",
            "timestamp": "2021-12-09T22:51:34.000000Z",
            "clientDevice": "desktop",
            "clientOsName": "Mac",
            "clientOsVersion": "10.15",
            "clientType": "browser",
            "clientName": "Chrome",
            "clientVersion": "95.0",
            "clientIsMobile": false,
            "clientIsBot": false
        }
  ]
}
```

```php
# paginate() method
$response = stream('pages')->paginate(1)->json();
```

```json
{
    "data": [
        {
            "_id": "61b28877a57f17655163cea2",
            "event": "video.started",
            "userId": 107,
            "customerId": 772,
            "hasContract": false,
            "customerAge": 34,
            "hasChildren": false,
            "persona": "mf",
            "timestamp": "2021-12-09T22:51:34.000000Z",
            "clientDevice": "desktop",
            "clientOsName": "Mac",
            "clientOsVersion": "10.15",
            "clientType": "browser",
            "clientName": "Chrome",
            "clientVersion": "95.0",
            "clientIsMobile": false,
            "clientIsBot": false
        }
    ],
    "meta": {
        "total": 304,
        "current_page": 1,
        "last_page": 304,
        "per_page": 1,
        "total_pages": 304,
        "count": 1,
        "execution_time": 0.05970406532287598
    }
}
```

```php
# count(), max(<column>), min(<column>), sum(<column>), avg(<column>) method
$response = stream('pages')->avg('customerAge')->json();
```

```json
{
  "result": 50.18421052631579
}
```

```php
# testing events 
$response = stream()->testEvent(['event' => 'test', '2983' => 12, 'test' => null])->json();
```

```json
{
    "status": "failed",
    "errors": {
        "2983": "2983 must only have alphabetical characters",
        "test": "test must only be a string, boolean or numeric value"
    }
}
```

```php
# validate events for collection 
$response = stream('purchases')->validateEvent(['event' => 'test'])->json();
```

```json
{
    "status": "failed",
    "errors": {
        "userId": "num",
        "itemId": "num",
        "itemName": "string",
        "itemQuantity": "num",
        "itemInStock": "bool"
    }
}
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

### Security

If you discover any security related issues, please email stefan@sriehl.com instead of using the issue tracker.

## Credits

- [Stefan Riehl](https://github.com/stefanriehl)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

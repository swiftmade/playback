# Idempotent endpoints in Laravel à la Stripe

[![Latest Version on Packagist](https://img.shields.io/packagist/v/swiftmade/playback.svg?style=flat-square)](https://packagist.org/packages/swiftmade/playback)
[![Build Status](https://img.shields.io/travis/swiftmade/playback/master.svg?style=flat-square)](https://travis-ci.org/swiftmade/playback)
[![Total Downloads](https://img.shields.io/packagist/dt/swiftmade/playback.svg?style=flat-square)](https://packagist.org/packages/swiftmade/playback)

Are you developing a sensitive API where calling the same endpoint twice can cause catastrophy? 💥

Here's how Stripe handles it:
- https://stripe.com/blog/idempotency
- https://stripe.com/docs/api/idempotent_requests

If you said "oh yes, that's smart" then read on. Because we implemented that for Laravel.

## Features

- Apply it to a single route, or apply to your whole API...
- Works only for POST requests. Other endpoints are ignored.
- Smart enough to verify path + headers + body is identical before returning the response.
- Will record and play back 2xx and 5xx responses, without touching your controller again.
- Doesn't remember the response if there was a validation error (4xx). So it's safe to retry.
- Prevents race conditions using Laravel's support for cache locks.


## Installation

You can install the package via composer:

```bash
composer require swiftmade/playback
```

To customize the configuration:

```bash
php artisan vendor:publish --provider="Swiftmade\Playback\PlaybackServiceProvider"
```

🚨 Important

Open `config/cache.php` and add a new item to the `stores` array.

```php
'stores' => [
    // ... other stores
    'playback' => [
        'driver' => 'redis',
        // We strongly recommend using a different
        // connection (another redis DB) in production.
        'connection' => 'cache',
    ],
]
```

## Use

1. The client must supply a idempotency key. Otherwise, the middleware won't execute.

```
Idempotency-Key: preferrably uuid4, but anything flies
```

2. The server will look the key up. If there's a match, exactly that response will be returned.

You can know that the response is a playback from the response headers:

```
Is-Playback: your idempotency key
```

3. If you get back status `400`, it means the following request was not identical with the cached one. Just use another idempotency key, if you mean to execute a fresh request.

4. If you get back status `425`, it means you retried too fast. It's perfectly safe to try again later.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email hello@swiftmade.co instead of using the issue tracker.

## Credits

- [Ahmet Özisik](https://github.com/swiftmade)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
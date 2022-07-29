# Laravel Playback

[![Latest Version on Packagist](https://img.shields.io/packagist/v/swiftmade/playback.svg?style=flat-square)](https://packagist.org/packages/swiftmade/playback)
![GitHub Actions](https://github.com/swiftmade/playback/actions/workflows/test.yml/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/swiftmade/playback.svg?style=flat-square)](https://packagist.org/packages/swiftmade/playback)

_Idempotent endpoints in Laravel à la Stripe._

Playback gives you idempotent endpoints in Laravel, using Redis locks. [What's even idempotency, and why should I care?](https://stripe.com/docs/api/idempotent_requests)

## Features

-   📼 Records and plays back 2xx and 5xx responses, without running your controller code again.
-   🔐 Built-in validation to prevent attacks by stolen/guessed idempotency keys.
-   ⚠️ Won't store the response if there was a validation error (4xx).
-   🏎 Prevents race conditions using atomical Redis locks.

## Installation

> 💡 Supports Laravel 8.x, Laravel 9.x on PHP 7.4, 8.0 or 8.1

1. You can install the package via composer:

```bash
composer require swiftmade/playback
```

2. Publish the config file (optional):

```bash
php artisan vendor:publish --provider="Swiftmade\Playback\PlaybackServiceProvider"
```

3. Add the playback cache store

Open `config/cache.php` and add a new store.

```php
'stores' => [
    // ... other stores
    'playback' => [
        'driver' => 'redis',
        // 👇🏻 Caution!
        // You probably don't want to use the cache connection in production.
        // Playback cache can grow to a big size for busy applications.
        // Make sure your redis instance is ready.
        'connection' => 'cache',
    ],
]
```

💡 **Apply the middleware**

Just apply the `Swiftmade\Playback\Playback` middleware to your endpoints. There are many ways of doing it, so here's a link to the docs:

-   https://laravel.com/docs/9.x/middleware

## Use

Even when middleware is active on a route, it's business as usual unless the client sends an `Idempotency-Key` in their request header.

```
Idempotency-Key: preferrably uuid4, but anything flies
```

Once Playback detects a key, it'll look it up in redis. If found, it will serve the same response **without hitting your controller action again**. You can know that happened by looking at the response headers. If it contains `Is-Playback`, you know it's just a repetition.

If the key is not found during the lookup, a race begins. The first request to acquire the redis lock gets to process the request and cache the response. Any other unlucky requests that land during that time window will return `425` status code.

#### Errors:

-   **400 Bad Request**
    If you get back status `400`, it means your request was not identical to the cached one. It's the client's responsibility to repeat the exact same request. This is also why another user can't steal a response just by stealing/guessing the idempotency key. The cookies/authentication token would be different, which fails the signature check.

-   **425 Too Early**
    If you get this error, it means you retried too fast after your initial attempt. Don't panic and try again a second later or so. It's perfectly safe to do so!

🚨 Pro tip: If your controller action returns 4xx or 3xx status code, Playback won't cache the response. It's your responsibility to ensure no side effects take place (or they are rolled back) if a validation fails, a related db record was not found, etc and therefore the response status is 4xx or 3xx.

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email hello@swiftmade.co instead of using the issue tracker.

## Credits

-   [Ahmet Özisik](https://github.com/swiftmade)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

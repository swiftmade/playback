# Idempotent Laravel Requests à la Stripe

[![Latest Version on Packagist](https://img.shields.io/packagist/v/swiftmade/idempotent.svg?style=flat-square)](https://packagist.org/packages/swiftmade/idempotent)
[![Build Status](https://img.shields.io/travis/swiftmade/idempotent/master.svg?style=flat-square)](https://travis-ci.org/swiftmade/idempotent)
[![Total Downloads](https://img.shields.io/packagist/dt/swiftmade/idempotent.svg?style=flat-square)](https://packagist.org/packages/swiftmade/idempotent)

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
- Doesn't remember the response if there was a valiation error (4xx). So it's safe to retry.
- Prevents race conditions using Laravel's support for cache locks.


## Installation

You can install the package via composer:

```bash
composer require swiftmade/idempotent
```

## Use

1. The client must pass the following header:

```
Idempotency-Key: --uuid or any other random string---
```

2. The server will check the key. If a matching response is already cached, exactly that response will be returned.

You can know that the response is a playback from the response headers:

```
Is-Playback: --the key you passed--
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
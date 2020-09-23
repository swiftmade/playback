<?php

namespace Swiftmade\Idempotent;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class RecordedResponses
{
    // Temporary...
    protected static $cache;

    public static function find($key): ?RecordedResponse
    {
        return static::store()->get($key);
    }

    public static function lock($key, Closure $closure)
    {
        return static::store()->lock($key)->get($closure);
    }

    /**
     * @param string $key
     * @param JsonResponse|Response $response
     */
    public static function save($key, $requestHash, $response)
    {
        static::store()->put(
            $key,
            new RecordedResponse(
                $key,
                $requestHash,
                $response
            ),
            config('idempotent.ttl')
        );
    }

    protected static function store()
    {
        return cache()->store(config('idempotent.cache_store'));
    }

    protected static function getRedisKey($key)
    {
        return 'ir.' . $key;
    }
}

<?php

namespace Swiftmade\Idempotent;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class RecordedResponses
{
    // Temporary...
    protected static $cache;

    public static function find($key): ?RecordedResponse
    {
        return self::$cache[$key] ?? null;
        /*
        return static::redis()->get(
            static::getRedisKey($key)
        );*/
    }

    public static function placehold($key)
    {
        self::$cache[$key] = RecordedResponse::placeholder($key);
    }

    /**
     * @param string $key
     * @param JsonResponse|Response $response
     */
    public static function record($key, $requestHash, $response)
    {
        self::$cache[$key] = new RecordedResponse(
            $key,
            $requestHash,
            $response
        );
    }

    public static function release($key)
    {
        unset(self::$cache[$key]);
    }

    /**
     * @return \Illuminate\Redis\Connections\Connection;
     */
    protected static function redis()
    {
        return resolve('idempotent.redis');
    }

    protected static function getRedisKey($key)
    {
        return 'ir.' . $key;
    }
}

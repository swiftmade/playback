<?php

namespace Swiftmade\Playback;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class Recorder
{
    // Temporary...
    protected static $cache;

    public static function find($key): ?RecordedResponse
    {
        return static::store()->get($key);
    }

    public static function race($key, Closure $winner, Closure $loser)
    {
        $lock = static::store()->lock($key);

        if ($lock->get()) {
            try {
                return $winner();
            } finally {
                $lock->release();
            }
        } else {
            return $loser();
        }
    }

    /**
     * @param string $key
     * @param string $requestHash
     * @param JsonResponse|Response $response
     */
    public static function save(string $key, string $requestHash, $response)
    {
        $playbackResponse = clone $response;
        $playbackResponse->header(config('playback.playback_header_name'), $key);

        static::store()->put(
            $key,
            RecordedResponse::fromResponse(
                $key,
                $requestHash,
                $playbackResponse
            ),
            config('playback.ttl')
        );
    }

    protected static function store()
    {
        return cache()->store(config('playback.cache_store'));
    }

    protected static function getRedisKey($key)
    {
        return 'ir.' . $key;
    }

    public static function flush()
    {
        self::store()->flush();
    }
}

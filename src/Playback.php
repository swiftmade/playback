<?php

namespace Swiftmade\Playback;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Playback
{
    public function handle(Request $request, Closure $next)
    {
        // Idempotency is only enforced on POST requests.
        if ($request->method() !== 'POST') {
            return $next($request);
        }

        // If the client did not provide a key, skip it.
        if (! ($key = $this->getIdempotencyKey($request))) {
            return $next($request);
        }

        // If cached, play back the response.
        if ($recordedResponse = Recorder::find($key)) {
            return $recordedResponse->playback(
                $this->requestHash($request)
            );
        }

        // The key doesn't exist yet... Start a race.
        return Recorder::race(
            $key,
            // Winner gets to process the request
            function () use ($key, $request, $next) {
                $response = $next($request);

                if ($this->isResponseRecordable($response)) {
                    Recorder::save(
                        $key,
                        $this->requestHash($request),
                        $response
                    );
                }

                return $response;
            },
            // Too early for the losers
            function () {
                return abort(425, 'Your request is still being processed.'
                    . 'You retried too early. You can safely retry later.');
            }
        );
    }

    protected function isResponseRecordable(Response $response): bool
    {
        $status = $response->getStatusCode();

        return ($status >= 200 && $status <= 299)
            || ($status >= 500 && $status <= 599);
    }

    protected function getIdempotencyKey(Request $request)
    {
        if ($key = $request->header(config('playback.header_name'))) {
            return $key;
        }
    }

    protected function requestHash(Request $request): string
    {
        // TODO: We may use a faster hashing function here...
        return md5(json_encode(
            [
                $request->path(),
                $request->all(),
                $request->headers->all(),
            ]
        ));
    }
}

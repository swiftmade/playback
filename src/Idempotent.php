<?php

namespace Swiftmade\Idempotent;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Idempotent
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

        // The key doesn't exist yet... Allow processing the request
        if (! ($recordedResponse = Recorder::find($key))) {

            // Prevent race conditions between two requests, with the same idempotence key
            return Recorder::race(
                $key,
                function () use ($key, $request, $next) {
                    // This request wins the race to process the request
                    // Now, actually process the request
                    $response = $next($request);

                    if ($this->isResponseRecordable($response)) {
                        // If the response is 2xx or 5xx, remember the response
                        Recorder::save(
                            $key,
                            $this->requestHash($request),
                            $response
                        );
                    }

                    return $response;
                },
                function () {
                    // This closure is called when there was a race condition
                    return abort(425, 'Your request is being processed.'
                        . 'You retried too early. You can safely retry later.');
                }
            );
        }

        return $recordedResponse->playback(
            $this->requestHash($request)
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
        if ($key = $request->header(config('idempotent.header_name'))) {
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

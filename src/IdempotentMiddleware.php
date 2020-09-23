<?php

namespace Swiftmade\Idempotent;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IdempotentMiddleware
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
        if (! ($recordedResponse = RecordedResponses::find($key))) {
            // Temporarily placehold this key, as we attempt to process this request
            RecordedResponses::placehold($key);

            // Actually process the request
            $response = $next($request);

            if ($this->isResponseRecordable($response)) {
                // If the response is 2xx or 5xx, remember the response
                RecordedResponses::record(
                    $key,
                    $this->requestHash($request),
                    $response
                );
            } else {
                // Otherwise, let go of the placehold
                RecordedResponses::release($key);
            }

            // Finally, return the response.
            return $response;
        }

        return $recordedResponse->playback(
            $this->requestHash($request)
        );
    }

    protected function isResponseRecordable(Response $response): bool
    {
        $status = $response->status();

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

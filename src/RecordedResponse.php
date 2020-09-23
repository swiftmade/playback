<?php

namespace Swiftmade\Playback;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class RecordedResponse
{
    public $key;
    public $body;
    public $status;
    public $headers;
    public $requestHash;

    public function __construct($key, $requestHash, $body, $status, $headers)
    {
        $this->key = $key;
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
        $this->requestHash = $requestHash;
    }

    /**
     * @param string $key
     * @param string $requestHash
     * @param Response|JsonResponse $response
     */
    public static function fromResponse($key, $requestHash, $response)
    {
        return new self(
            $key,
            $requestHash,
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }

    public function playback($requestHash)
    {
        if ($requestHash !== $this->requestHash) {
            abort(400, 'Keys for idempotent requests can only be used with '
                . 'the same parameters they were first used with.'
                . 'Try using a key other than \'' . e($this->key) . '\' if'
                . 'you meant to execute a different request.');
        }

        return response($this->body, $this->status, $this->headers);
    }
}

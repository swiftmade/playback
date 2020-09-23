<?php

namespace Swiftmade\Idempotent;

class RecordedResponse
{
    private $key;
    private $requestHash;
    private $response;

    public function __construct($key, $requestHash = null, $response = null)
    {
        $this->key = $key;
        $this->requestHash = $requestHash;
        $this->response = $response;
    }

    public static function placeholder($key)
    {
        return new self($key);
    }

    public function playback($requestHash)
    {
        if ($requestHash !== $this->requestHash) {
            abort(400, 'Keys for idempotent requests can only be used with '
                . 'the same parameters they were first used with.'
                . 'Try using a key other than \'' . e($this->key) . '\' if'
                . 'you meant to execute a different request.');
        }

        return $this->response->header(
            config('idempotent.playback_header_name'),
            $this->key
        );
    }
}

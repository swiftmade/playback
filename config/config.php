<?php

return [
    /*
 * Set IDEMPOTENT_DISABLED=true to turn off the library
 * for development/testing purposes etc.
 */
    'disabled' => env('IDEMPOTENT_DISABLED', false),

    /*
 * How long should idempotency keys survive (in seconds)?
 * The default is set to 1 day.
     */
    'ttl' => 86400,

    /*
     * Where to look for the idempotency key
     */
    'header_name' => 'idempotency-key',

    /*
     * If the response is a playback,
     * this header will be set
     */
    'playback_header_name' => 'is-playback',

    /*
     * If you want to create a separate cache store
     * for idempotency records, this is the place to do it.
     *
     * Please see config/cache.php for more details
     *
     */
    'cache_store' => 'redis',
];

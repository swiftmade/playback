<?php

return [
    /*
     * Set IDEMPOTENT_DISABLED=true to turn off the library
     * for development/testing purposes etc.
     */
    'disabled' => env('IDEMPOTENT_DISABLED', false),

    /*
     * How long should idempotency keys survive (in minutes)?
     * The default is set to 1 day.
     */
    'lifetime' => 1440,

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
     * Which redis connection to use to store idempotency keys and responses?
     *
     * To add a different connection, please modify
     * the redis config under config/database.php
     */
    'redis_connection' => 'default',
];

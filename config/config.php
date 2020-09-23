<?php

return [

    /*
     * Which redis connection to use to store idempotency keys and responses?
     *
     * To add a different connection, please modify
     * the redis config under config/database.php
     */
    'redis_connection' => 'default',

    /*
     * To avoid response hijacking, this library suggests
     * prepending the currently authenticated user's id
     * to the idempotency key transparently.
     */
    'prepend_keys_with_user_id' => true,

    /*
     * How long should idempotency keys survive (in minutes)?
     * The default is set to 1 day.
     */
    'lifetime' => 1440,
];

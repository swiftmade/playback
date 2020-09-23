<?php

namespace Swiftmade\Idempotent;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Swiftmade\Idempotent\Skeleton\SkeletonClass
 */
class IdempotentFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'idempotent';
    }
}

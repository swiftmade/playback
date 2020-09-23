<?php

namespace Swiftmade\Idempotent\Tests;

use Orchestra\Testbench\TestCase;
use Swiftmade\Idempotent\IdempotentServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [IdempotentServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}

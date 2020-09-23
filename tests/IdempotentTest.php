<?php

namespace Swiftmade\Idempotent\Tests;

use Orchestra\Testbench\TestCase;
use Swiftmade\Idempotent\Recorder;
use Swiftmade\Idempotent\IdempotentServiceProvider;
use Swiftmade\Idempotent\Tests\Support\TestServiceProvider;

class IdempotentTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            IdempotentServiceProvider::class,
            TestServiceProvider::class,
        ];
    }

    protected function tearDown(): void
    {
        Recorder::flush();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_plays_back_post_requests()
    {
        $headers = [
            config('idempotent.header_name') => ($key = uniqid('key_')),
        ];

        $response = $this->post('users', [], $headers);

        $response->assertStatus(200);
        $response->assertSee('Created user');
        // The first response is not a playback
        $response->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Repeat the request
        $response2 = $this->post('users', [], $headers);
        $response2->assertStatus(200);
        $response2->assertHeader(
            config('idempotent.playback_header_name'),
            $key
        );

        // Contents are identical!
        $this->assertEquals(
            $response->getContent(),
            $response2->getContent()
        );
    }

    /**
     * @test
     */
    public function it_plays_back_internal_server_errors()
    {
        $headers = [
            config('idempotent.header_name') => ($key = uniqid('key_')),
        ];

        $response = $this->post('server_error', [], $headers);

        $response->assertStatus(500);
        // The first response is not a playback
        $response->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Repeat the request
        $response2 = $this->post('server_error', [], $headers);
        $response2->assertStatus(500);
        $response2->assertHeader(
            config('idempotent.playback_header_name'),
            $key
        );

        // Contents are identical!
        $this->assertEquals(
            $response->getContent(),
            $response2->getContent()
        );
    }

    /**
     * @test
     */
    public function different_key_returns_different_response()
    {
        $headers = [
            config('idempotent.header_name') => ($key = uniqid('key_')),
        ];

        $response = $this->post('users', [], $headers);
        $response->assertStatus(200);
        $response->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Regenerate the idempotency key
        $headers = [
            config('idempotent.header_name') => ($key = uniqid('key_')),
        ];

        // Repeat the request
        $response2 = $this->post('users', [], $headers);
        $response2->assertStatus(200);
        // This is also not a playback, because idempotency key has changed!
        $response->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Contents are identical!
        $this->assertNotEquals(
            $response->getContent(),
            $response2->getContent()
        );
    }

    /**
     * @test
     */
    public function it_returns_400_if_headers_change()
    {
        $headers = [
            config('idempotent.header_name') => ($key = uniqid('key_')),
        ];

        $response = $this->post('users', [], $headers);
        $response->assertStatus(200);
        $response->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Repeat the request
        $response2 = $this->post('users', [], $headers + [
            'another-header' => 'this was not in the previous request',
        ]);

        $response2->assertStatus(400);
    }

    /**
     * @test
     */
    public function it_returns_400_if_body_changes()
    {
        $headers = [
            config('idempotent.header_name') => ($key = uniqid('key_')),
        ];

        $response = $this->post('users', [], $headers);
        $response->assertStatus(200);
        $response->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Repeat the request
        $response2 = $this->post('users', [
            'var' => 'This variable was not in the request body before',
        ], $headers);

        $response2->assertStatus(400);
    }

    /**
     * @test
     */
    public function it_returns_400_if_query_parameters_change()
    {
        $headers = [
            config('idempotent.header_name') => ($key = uniqid('key_')),
        ];

        $response = $this->post('users', [], $headers);
        $response->assertStatus(200);
        $response->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Repeat the request
        $response2 = $this->post('users?query=x', [], $headers);
        $response2->assertStatus(400);
    }

    /**
     * @test
     */
    public function it_returns_400_if_path_changes()
    {
        $headers = [
            config('idempotent.header_name') => ($key = uniqid('key_')),
        ];

        $response = $this->post('users', [], $headers);
        $response->assertStatus(200);
        $response->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Repeat the request
        $response2 = $this->post('books', [], $headers);
        $response2->assertStatus(400);
    }

    /**
     * @test
     */
    public function it_does_not_play_back_if_validation_fails()
    {
        $headers = [
            config('idempotent.header_name') => 'validation_test',
        ];

        $response = $this->postJson('validate', ['name' => ''], $headers);
        $response->assertStatus(422);

        // Repeat the request, this time it will succeed
        $response2 = $this->postJson('validate', ['name' => 'ahmet'], $headers);
        $response2->assertStatus(200);
        $response2->assertHeaderMissing(config('idempotent.playback_header_name'));

        $this->assertNotEquals(
            $response->getContent(),
            $response2->getContent()
        );

        // Repeat the request, but with a different body
        $response3 = $this->postJson('validate', ['name' => 'ahmet2'], $headers);
        // It returns 400, because body has changed
        $response3->assertStatus(400);
        $response3->assertHeaderMissing(config('idempotent.playback_header_name'));

        // Repeat the request, but with identical body as request2
        $response4 = $this->postJson('validate', ['name' => 'ahmet'], $headers);
        $response4->assertStatus(200);
        $response4->assertHeader(config('idempotent.playback_header_name'), 'validation_test');
    }

    /**
     * @test
     */
    public function it_does_not_record_get_request()
    {
        $headers = [
            config('idempotent.header_name') => 'test',
        ];

        $response = $this->get('get', $headers);

        $response->assertStatus(200);
        $response->assertSee('Get response');

        // Repeat the request
        $response2 = $this->get('get', $headers);
        $response2->assertStatus(200);
        $response2->assertHeaderMissing(config('idempotent.playback_header_name'));

        $this->assertNotEquals(
            $response->getContent(),
            $response2->getContent()
        );
    }
}

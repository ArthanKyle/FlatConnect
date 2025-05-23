<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\Tplink\ClientDiscoveryService;

class ClientDiscoveryServiceTest extends TestCase
{
    protected ClientDiscoveryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClientDiscoveryService();
    }

    public function test_blockClient_success()
    {
        // Mock the login HTTP POST call to return successful response
        Http::fake([
            $this->service->baseUrl . '/' => Http::response('', 200), // login successful
            $this->service->baseUrl . '/data/status.client.blocklist.json' => Http::response('', 200), // block client successful
        ]);

        $result = $this->service->blockClient('AA:BB:CC:DD:EE:FF');
        $this->assertTrue($result);
    }

    public function test_blockClient_login_failed()
    {
        // Mock login POST call to fail
        Http::fake([
            $this->service->baseUrl . '/' => Http::response('', 401), // login failed
        ]);

        $result = $this->service->blockClient('AA:BB:CC:DD:EE:FF');
        $this->assertFalse($result);
    }

    public function test_unblockClient_success()
    {
        // Mock login POST call and unblock client POST call
        Http::fake([
            $this->service->baseUrl . '/' => Http::response('', 200), // login success
            $this->service->baseUrl . '/data/status.client.blocklist.json' => Http::response('', 200), // unblock success
        ]);

        $result = $this->service->unblockClient('AA:BB:CC:DD:EE:FF');
        $this->assertTrue($result);
    }

    public function test_unblockClient_login_failed()
    {
        // Mock login POST call to fail
        Http::fake([
            $this->service->baseUrl . '/' => Http::response('', 401), // login failed
        ]);

        $result = $this->service->unblockClient('AA:BB:CC:DD:EE:FF');
        $this->assertFalse($result);
    }
}
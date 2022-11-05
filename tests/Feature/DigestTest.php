<?php

namespace Tests\Feature;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DigestTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Test subscription creation.
     */
    public function testCreateSubscription() {
        Config::set('subscriptions', ['https://itinerare.net/feeds/programming']);
        $this->artisan('update-subscriptions')->assertExitCode(0);

        $this->assertDatabaseHas('subscriptions', [
            'url' => 'https://itinerare.net/feeds/programming',
        ]);
    }

    /**
     * Test digest creation using a live feed.
     *
     * @dataProvider digestProvider
     *
     * @param bool $summaryOnly
     */
    public function testCreateDigest($summaryOnly) {
        Config::set('subscriptions', ['https://itinerare.net/feeds/programming']);
        $this->artisan('update-subscriptions')->assertExitCode(0);

        $status = (new Subscription)->createDigests($summaryOnly, Carbon::parse(01 / 01 / 2000));

        $this->assertTrue($status);
        $this->assertDatabaseHas('digests', [
            'url' => 'https://itinerare.net/feeds/programming',
        ]);
    }

    public function digestProvider() {
        return [
            'summary only'  => [1],
            'full contents' => [0],
        ];
    }
}

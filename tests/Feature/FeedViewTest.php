<?php

namespace Tests\Feature;

use App\Models\Digest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class FeedViewTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
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
        $status = (new Digest)->createDigests($summaryOnly, Carbon::parse(01 / 01 / 2000));

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

    /**
     * Test feed access.
     *
     * @dataProvider feedProvider
     *
     * @param int   $status
     * @param mixed $withDigest
     */
    public function testGetFeed($withDigest, $status) {
        if ($withDigest) {
            $digest = Digest::factory()->create();
        }

        $response = $this->get('/')
            ->assertStatus($status);

        if ($withDigest && $status == 200) {
            $response->assertSee($digest->url);
        }
    }

    public function feedProvider() {
        return [
            'feed'             => [0, 200],
            'feed with digest' => [1, 200],
        ];
    }
}

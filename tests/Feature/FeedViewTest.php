<?php

namespace Tests\Feature;

use App\Models\Digest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FeedViewTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        PUBLIC: RSS FEEDS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();
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

        $response = $this->get('feed')
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

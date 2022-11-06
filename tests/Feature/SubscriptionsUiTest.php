<?php

namespace Tests\Feature;

use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SubscriptionsUiTest extends TestCase {
    use RefreshDatabase, WithFaker;

    protected function setUp(): void {
        parent::setUp();

        Config::set('procyon-settings.web-interface', true);
    }

    /**
     * Test subscription UI access.
     *
     * @dataProvider subscriptionViewProvider
     *
     * @param int   $status
     * @param mixed $withSubscription
     */
    public function testGetSubscriptionUi($withSubscription, $status) {
        if ($withSubscription) {
            $subscription = Subscription::factory()->create();
        }

        $response = $this->get('subscriptions')
            ->assertStatus($status);

        if ($withSubscription && $status == 200) {
            $response->assertSee($subscription->url);
        }
    }

    public function subscriptionViewProvider() {
        return [
            'empty'             => [0, 200],
            'with subscription' => [1, 200],
        ];
    }

    /**
     * Test managing subscriptions.
     *
     * @dataProvider subscriptionEditProvider
     *
     * @param array $existing
     * @param bool  $new
     */
    public function testPostSubscriptions($existing, $new) {
        $data = [];
        if ($existing) {
            $subscription = Subscription::factory()->create();
            if ($existing[1]) {
                $data['url'][$subscription->id] = $subscription->url;
            }
        }
        if ($new) {
            $data['url'][] = $this->faker()->url;
        }

        $this->post('subscriptions', $data)
            ->assertStatus(302);

        if ($existing && $existing[1]) {
            $this->assertModelExists($subscription);
        } elseif ($existing && $existing[0]) {
            $this->assertModelMissing($subscription);
        }

        if ($new) {
            $this->assertDatabaseHas('subscriptions', [
                'url' => end($data),
            ]);
        }
    }

    public function subscriptionEditProvider() {
        return [
            //'default'               => [null, 0],
            'with existing'         => [[1, 1], 0],
            'remove existing'       => [[1, 0], 0],
            'with new'              => [null, 1],
            'with both'             => [[1, 1], 1],
            'both, remove existing' => [[1, 0], 1],
        ];
    }
}

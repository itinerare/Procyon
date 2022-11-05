<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class UpdateSubscriptions extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the subscriptions table with data from the associated config file as necessary.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        // Add new subscriptions
        foreach (config('subscriptions') as $feed) {
            if (!Subscription::where('url', $feed)->exists()) {
                Subscription::create([
                    'url' => $feed,
                ]);
            }
        }

        // Remove any that are on record but absent from the config file
        // Note that this does not delete old digests!
        $subscriptions = Subscription::pluck('url')->toArray();
        $missing = array_merge(array_diff(config('subscriptions'), $subscriptions), array_diff($subscriptions, config('subscriptions')));

        if (count($missing)) {
            foreach ($missing as $sub) {
                $subscription = Subscription::where('url', $sub)->first();
                if ($subscription) {
                    // $subscription->digests->delete();
                    $subscription->delete();
                }
            }
        }

        return Command::SUCCESS;
    }
}

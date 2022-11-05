<?php

namespace App\Console\Commands;

use App\Models\Digest;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProcyonV2 extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-procyon-v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates existing data to v2 formats.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $this->info('************************');
        $this->info('* UPDATE PROCYON TO V2 *');
        $this->info('************************'."\n");

        $this->info('This command should be run after updating packages using composer.');
        if ($this->confirm('Have you run the composer install command or equivalent?')) {
            // Run migrations as a prerequisite for updating subscription information
            $this->line('Running migrations...');
            $this->call('migrate');

            if (env('APP_ENV') == 'production') {
                $this->line("\n".'Clearing and optimizing caches...');
                $this->call('optimize');
            }

            // In order to migrate data, it's necessary to first
            // migrate subscriptions defined via the config file to the table
            $this->line("\n".'Updating subscriptions...');
            $this->call('update-subscriptions');
            $this->info('Subscriptions updated!');

            // Match existing digests to subscriptions via URL
            $this->line("\n".'Linking digests to subscriptions...');
            foreach (Digest::whereNull('subscription_id')->get() as $digest) {
                if (Subscription::where('url', $digest->url)->exists()) {
                    $digest->update([
                        'subscription_id' => Subscription::where('url', $digest->url)->first()->id,
                    ]);
                }
            }
            $this->info('Digests linked!');

            // This should only be performed if the data has not already been
            // migrated and the column deleted!
            if (Schema::hasColumn('digests', 'last_entry')) {
                // Fetch last_entry info from each subscription's latest digest
                // and move it to the subscription; in the future, this saves the
                // trouble of finding the most recent digest to check it
                $this->line("\n".'Finding last entry information for subscriptions...');
                foreach (Subscription::with('digests')->get() as $subscription) {
                    if ($subscription->digests->sortByDesc('created_at')->count()) {
                        $subscription->update([
                            'last_entry' => $subscription->digests->sortByDesc('created_at')->first()->last_entry ?? null,
                        ]);
                    }
                }
                $this->info('Last entry information migrated!');

                // Once data is migrated, the last_entry column can be safely
                // dropped from digests, keeping the table clean
                $this->line("\n".'Cleaning a defunct column from the digests table...');
                Schema::table('digests', function (Blueprint $table) {
                    $table->dropColumn('last_entry');
                });
                $this->info('Digests table cleaned!');
            } else {
                $this->line("\n".'Skipped: Finding last entry information (column no longer exists)');
            }

            return Command::SUCCESS;
        }

        return Command::SUCCESS;
    }
}

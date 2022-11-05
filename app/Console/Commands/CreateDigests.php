<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CreateDigests extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-digests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates daily digests for each subscription.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        // Subscriptions should be updated before digests are created
        // so that any new subscriptions can receive digests and removed ones
        // not, etc.
        $this->call('update-subscriptions');

        if (!(new Subscription)->createDigests(config('procyon-settings.summary-only'))) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

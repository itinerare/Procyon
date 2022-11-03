<?php

namespace App\Console\Commands;

use App\Models\Digest;
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
    protected $description = 'Generates daily digests for configured feeds.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        if (!(new Digest)->createDigests(config('feed.feeds.main.summary-only'))) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

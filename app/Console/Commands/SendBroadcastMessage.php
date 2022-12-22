<?php

namespace App\Console\Commands;

use App\Jobs\SendBroadcastMessageJob;
use Illuminate\Console\Command;

use App\Models\BroadcastMessage;

class SendBroadcastMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking data in broadcast_messages table and send message to user.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bm = new BroadcastMessage();
        $bm = $bm->where('scheduled_date', now()->format('Y-m-d'))
            ->where('scheduled_time', now()->format('H:i'));

        $bm = $bm->get();

        if ($bm->count() > 0) {
            foreach ($bm as $b) {
                $b = $b->load(['BroadcastMessageRecipients']);
                foreach ($b->BroadcastMessageRecipients as $br) {
                    // send with job.
                    $job = (new SendBroadcastMessageJob($b, $br));
                    dispatch($job);
                }
            }
        }

        return Command::SUCCESS;
    }
}

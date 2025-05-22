<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanExpiredResetTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-reset-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
   public function handle()
{
    DB::table('password_reset_tokens')
        ->where('created_at', '<', now()->subMinutes(15))
        ->delete();

    $this->info('token hết hạn đã được xóa..');
}
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Token;

class ExpirePassportTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-passport-tokens';

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
        Token::where('revoked', false)->update(['revoked' => true]);

        $this->info('All Passport tokens have been revoked.');
    }
}
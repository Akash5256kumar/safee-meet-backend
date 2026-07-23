<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Verification\TrustScoreCalculator;
use Illuminate\Console\Command;

class RecalculateTrustScores extends Command
{
    protected $signature = 'trust-score:recalculate';

    protected $description = 'Recalculate every user\'s trust_score from their current verification_level';

    public function handle(): int
    {
        $count = 0;

        User::query()->chunkById(200, function ($users) use (&$count) {
            foreach ($users as $user) {
                TrustScoreCalculator::recalculate($user);
                $count++;
            }
        });

        $this->info("Recalculated trust_score for {$count} users.");

        return self::SUCCESS;
    }
}

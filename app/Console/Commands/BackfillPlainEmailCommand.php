<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Throwable;

class BackfillPlainEmailCommand extends Command
{
    protected $signature = 'users:backfill-plain-email {--dry-run : Report what would change without saving}';

    protected $description = 'Decrypts each user\'s email_encrypted into the new plain email column';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $users = User::whereNotNull('email_encrypted')->whereNull('email')->get();

        $this->info("Found {$users->count()} user(s) with an encrypted email but no plain email set.");

        $updated = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $decrypted = decrypt($user->email_encrypted);
            } catch (Throwable $e) {
                $failed++;
                $this->warn("  [SKIP] user {$user->id}: could not decrypt email_encrypted ({$e->getMessage()})");
                continue;
            }

            $this->line("  user {$user->id}: {$decrypted}" . ($dryRun ? ' (dry run — not saved)' : ''));

            if (!$dryRun) {
                $user->forceFill(['email' => $decrypted])->save();
            }
            $updated++;
        }

        $this->info("Done. Updated: {$updated}. Failed/skipped: {$failed}.");

        return self::SUCCESS;
    }
}

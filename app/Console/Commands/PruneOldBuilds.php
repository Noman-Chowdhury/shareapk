<?php

namespace App\Console\Commands;

use App\Models\Build;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneOldBuilds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'builds:prune {days=30 : Number of days to keep rejected builds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove APK files of rejected builds older than X days to save storage.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->argument('days');
        $date = now()->subDays($days);

        $oldBuilds = Build::where('status', 'Rejected')
            ->where('created_at', '<', $date)
            ->get();

        if ($oldBuilds->isEmpty()) {
            $this->info("No old rejected builds found to prune.");
            return;
        }

        $count = 0;
        foreach ($oldBuilds as $build) {
            if ($build->file_path && Storage::disk('public')->exists($build->file_path)) {
                Storage::disk('public')->delete($build->file_path);
                // We keep the database record but null the file path to save space
                $build->update(['file_path' => null]);
                $count++;
            }
        }

        $this->info("Successfully pruned {$count} old rejected builds.");
    }
}

<?php

namespace App\Console\Commands;

use App\Install\ReleaseBuilder;
use Illuminate\Console\Command;
use Throwable;

class Release extends Command
{
    protected $signature = 'cms:release
        {--output= : Folder to write to (default storage/app/releases)}
        {--ref=HEAD : The git commit or branch to build from}
        {--force : Build even if there are uncommitted changes}';

    protected $description = 'Build the release package: an installable zip of the CMS plus a zip of each optional module';

    public function handle(ReleaseBuilder $Builder): int
    {
        try {
            $changes = $Builder->uncommittedChanges();
            if ($changes && !$this->option('force')) {
                $this->warn('These files have uncommitted changes, which won\'t be in the release:');
                foreach ($changes as $change) {
                    $this->line('  ' . $change);
                }
                if (!$this->input->isInteractive() || !$this->confirm('Build from the last commit anyway?', true)) {
                    $this->line('Cancelled. Commit your changes, or use --force.');
                    return self::FAILURE;
                }
            }

            $result = $Builder
                ->onProgress(fn (string $line) => $this->line('  ' . $line))
                ->build($this->option('output') ?: null, (string) $this->option('ref'));
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Release ' . $result['version'] . ' (commit ' . $result['commit'] . ') is ready:');
        $this->line('  ' . $result['zip']);
        $this->line('  sha256 ' . $result['sha256']);
        $this->line('  Optional modules: ' . implode(', ', $result['modules']));

        return self::SUCCESS;
    }
}

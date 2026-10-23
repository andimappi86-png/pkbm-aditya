<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureDocumentStorageCommand extends Command
{
    protected $signature = 'documents:secure-storage
                            {--dry-run : Simulate file migration without modifying disk}
                            {--keep-source : Keep source file on public disk after copying}';

    protected $description = 'Migrate legacy document files from public disk to private local disk with SHA-256 verification';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $keepSource = (bool) $this->option('keep-source');

        $this->info('Starting document storage security migration...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE: No files will be copied or deleted.');
        }

        // Ensure private documents folder exists on local disk
        if (! Storage::disk('local')->exists('documents')) {
            if (! $dryRun) {
                Storage::disk('local')->makeDirectory('documents');
            }
        }

        // Collect all legacy files from public disk and DB records
        $publicFiles = Storage::disk('public')->files('documents');
        $documents = Document::all();

        $filesToCheck = collect($publicFiles);
        foreach ($documents as $doc) {
            if ($doc->file_path && ! $filesToCheck->contains($doc->file_path)) {
                if (Storage::disk('public')->exists($doc->file_path)) {
                    $filesToCheck->push($doc->file_path);
                }
            }
        }

        $filesToCheck = $filesToCheck->unique()->values();

        if ($filesToCheck->isEmpty()) {
            $this->info('No legacy files found in public storage. Storage is already secure.');

            return self::SUCCESS;
        }

        $this->info("Found {$filesToCheck->count()} legacy file(s) in public storage.");

        $migrated = 0;
        $alreadySecure = 0;
        $failed = 0;

        $rows = [];

        foreach ($filesToCheck as $filePath) {
            $sourceExists = Storage::disk('public')->exists($filePath);
            $localExists = Storage::disk('local')->exists($filePath);

            if (! $sourceExists) {
                if ($localExists) {
                    $alreadySecure++;
                    $rows[] = [$filePath, 'Already in private disk', 'OK'];
                } else {
                    $failed++;
                    $rows[] = [$filePath, 'Missing from both disks', 'ERROR'];
                }
                continue;
            }

            $sourceContents = Storage::disk('public')->get($filePath);
            $sourceSize = strlen($sourceContents);
            $sourceHash = hash('sha256', $sourceContents);

            if ($localExists) {
                $localContents = Storage::disk('local')->get($filePath);
                $localHash = hash('sha256', $localContents);

                if (hash_equals($sourceHash, $localHash)) {
                    if (! $keepSource && ! $dryRun) {
                        Storage::disk('public')->delete($filePath);
                        $rows[] = [$filePath, 'Matched local copy; deleted public source', 'SECURED'];
                    } else {
                        $rows[] = [$filePath, 'Already matched local copy', 'VERIFIED'];
                    }
                    $alreadySecure++;
                    continue;
                }
            }

            // Copy to local private disk
            if (! $dryRun) {
                $copied = Storage::disk('local')->put($filePath, $sourceContents);
                if (! $copied) {
                    $this->error("Failed writing destination for: {$filePath}");
                    $failed++;
                    $rows[] = [$filePath, 'Write failed', 'ERROR'];
                    continue;
                }

                // Verify destination byte-by-byte and hash
                $destContents = Storage::disk('local')->get($filePath);
                $destSize = strlen($destContents);
                $destHash = hash('sha256', $destContents);

                if (! hash_equals($sourceHash, $destHash) || $sourceSize !== $destSize) {
                    $this->error("Checksum mismatch for {$filePath}! Aborting deletion.");
                    $failed++;
                    $rows[] = [$filePath, 'Checksum mismatch', 'ERROR'];
                    continue;
                }

                // Only delete source after copy is verified
                if (! $keepSource) {
                    Storage::disk('public')->delete($filePath);
                    $status = 'MIGRATED & UNLINKED FROM PUBLIC';
                } else {
                    $status = 'COPIED & VERIFIED (SOURCE KEPT)';
                }
            } else {
                $status = 'DRY RUN - READY TO MIGRATE';
            }

            $migrated++;
            $rows[] = [$filePath, 'SHA-256: '.substr($sourceHash, 0, 16).'...', $status];
        }

        $this->table(['File Path', 'Detail', 'Status'], $rows);

        $this->info("Migration summary: {$migrated} migrated, {$alreadySecure} already secure, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

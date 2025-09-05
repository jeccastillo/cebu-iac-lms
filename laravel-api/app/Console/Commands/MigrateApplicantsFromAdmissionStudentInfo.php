<?php

namespace App\Console\Commands;

use App\Services\Admissions\ApplicantsMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MigrateApplicantsFromAdmissionStudentInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     *  --dry-run               Do not write, only print summary
     *  --limit=INT             Cap processed source rows (pre-dedupe)
     *  --since=YYYY-MM-DD      Filter source created_at >= since (if column exists)
     *  --email=STRING          Only process this email
     *  --source-connection=DB  DB connection name for admission_student_information (defaults to default connection)
     */
    protected $signature = 'applicants:migrate-from-sms
        {--dry-run : Do not write, print summary only}
        {--limit= : Cap processed source rows (pre-dedupe)}
        {--since= : Include rows with created_at >= this date (YYYY-MM-DD)}
        {--email= : Only process a specific email}
        {--source-connection= : Source DB connection name for admission_student_information}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate applicants from legacy admission_student_information into tb_mas_applicant_data JSON snapshots.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit');
        $limit = is_numeric($limit) ? (int)$limit : null;
        $since = $this->option('since');
        $email = $this->option('email');
        $sourceConn = $this->option('source-connection');

        $this->info('=== Applicants Migration (admission_student_information -> tb_mas_applicant_data) ===');
        $this->line('Options: dry-run=' . ($dryRun ? 'yes' : 'no') . ', limit=' . ($limit ?? 'none') . ', since=' . ($since ?? 'none') . ', email=' . ($email ?? 'none') . ', source-connection=' . ($sourceConn ?? 'default'));

        // Initialize service
        $service = new ApplicantsMigrationService($sourceConn);

        // Preflight
        $this->line('');
        $this->info('Preflight checks...');
        $pf = $service->preflight();

        // Print preflight summary
        $this->line('Connections: source=' . Arr::get($pf, 'connections.source') . ', target=' . Arr::get($pf, 'connections.target'));
        $this->preflightTable('Source table', Arr::get($pf, 'source.table'), Arr::get($pf, 'source.exists'), Arr::get($pf, 'source.missing', []));
        $this->preflightTable('Users table', Arr::get($pf, 'users.table'), Arr::get($pf, 'users.exists'), Arr::get($pf, 'users.missing', []));
        $this->preflightTable('Applicant data table', Arr::get($pf, 'applicant_data.table'), Arr::get($pf, 'applicant_data.exists'), Arr::get($pf, 'applicant_data.missing', []));

        if (!$pf['ok']) {
            $this->error('Preflight failed: required tables/columns are missing.');
            if (!$dryRun) {
                $this->warn('Aborting because this is not a dry-run. Re-run with --dry-run to see more details or fix schema issues first.');
                return Command::FAILURE;
            }
            $this->warn('Continuing in dry-run mode for diagnostics.');
        }

        // Fetch candidates from source
        $this->line('');
        $this->info('Fetching source rows...');
        try {
            $rows = $service->fetchSourceRows($since, $email, $limit);
        } catch (\Throwable $e) {
            $this->error('Failed to fetch source rows: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $totalCandidates = $rows->count();
        $this->line("Candidates fetched (pre-dedupe): {$totalCandidates}");

        // Dedupe by latest per email
        $deduped = $service->dedupeLatestPerEmail($rows);
        $uniqueEmails = $deduped->count();
        $this->line("Unique emails after dedupe: {$uniqueEmails}");

        // Iterate and prepare inserts / skips
        $resolvedUsers = 0;
        $noEmail = 0;
        $unresolved = 0;
        $alreadyHasData = 0;
        $toInsert = 0;
        $inserted = 0;
        $createdUsers = 0;

        // Optionally capture small sample for display
        $sample = [];

        /** @var Collection $deduped */
        foreach ($deduped as $idx => $row) {
            //print_r($deduped);
            //die();            
            $rowEmail = $this->getField($row, ['email', 'strEmail']);
            if (!$rowEmail || trim($rowEmail) === '') {
                $noEmail++;
                continue;
            }

            $userId = $service->resolveUserIdByEmail(trim($rowEmail));

            // If user does not exist, create it (unless dry-run or preflight failed)
            
            if (!$userId && !$dryRun && $pf['ok']) {                    
                try {
                    $newId = $service->createOrGetUserIdFromRow($row);
                    if ($newId) {
                        $userId = $newId;
                        $createdUsers++;
                    }                                                      
                } catch (\Throwable $e) {
                    echo "Caught exception: " . $e->getMessage();
                    // fallthrough to unresolved
                                
                }
                
            }

            if (!$userId) {
                $unresolved++;
                continue;
            }
            $resolvedUsers++;

            if ($service->hasAnyApplicantData($userId)) {
                $alreadyHasData++;
                continue;
            }

            $json = $service->normalizeRow($row);
            $toInsert++;

            if (count($sample) < 5) {
                $sample[] = [
                    'user_id' => $userId,
                    'email' => $rowEmail,
                    'json_keys' => array_keys($json),
                ];
            }

            if (!$dryRun && $pf['ok']) {
                try {
                    $service->insertSnapshot($userId, $json, $row);
                    $inserted++;
                } catch (\Throwable $e) {
                    $this->error("Insert failed for user_id={$userId}, email={$rowEmail}: " . $e->getMessage());
                }
            }
        }

        // Summary
        $this->line('');
        $this->info('=== Migration Summary ===');
        $this->line('Candidates (pre-dedupe): ' . $totalCandidates);
        $this->line('Unique emails: ' . $uniqueEmails);
        $this->line('Resolved users: ' . $resolvedUsers);
        $this->line('Created users: ' . $createdUsers);
        $this->line('Missing email: ' . $noEmail);
        $this->line('Unresolved users by email: ' . $unresolved);
        $this->line('Already have applicant_data: ' . $alreadyHasData);
        $this->line('To insert: ' . $toInsert);
        if ($dryRun) {
            $this->line('Inserted (dry-run): 0');
        } else {
            $this->line('Inserted: ' . $inserted);
        }

        // Show sample preview
        if (!empty($sample)) {
            $this->line('');
            $this->info('Sample (up to 5) of rows to insert:');
            foreach ($sample as $s) {
                $this->line('- user_id=' . $s['user_id'] . ', email=' . $s['email'] . ', top-level JSON keys=' . implode(',', $s['json_keys']));
            }
        }

        $this->line('');
        $this->info('Done.');
        return Command::SUCCESS;
    }

    protected function preflightTable(string $label, string $table, bool $exists, array $missing): void
    {
        $status = $exists ? 'OK' : 'MISSING';
        $this->line(sprintf('%s: %s (%s)', $label, $table, $status));
        if (!empty($missing)) {
            $this->warn('  Missing columns: ' . implode(', ', $missing));
        }
    }

    protected function getField(object $row, array $candidates)
    {
        foreach ($candidates as $key) {
            if (is_object($row) && property_exists($row, $key)) {
                return $row->{$key};
            }
        }
        return null;
    }
}

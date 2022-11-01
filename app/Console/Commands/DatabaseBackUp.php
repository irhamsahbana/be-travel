<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class DatabaseBackUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create copy of mysql dump for existing database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Create backup folder and set permission if not exist.
        $storageAt = storage_path() . "/app/backup/";
        if(!File::exists($storageAt))
            File::makeDirectory($storageAt, 0755, true, true);

        $dbDumpPath = env('DB_DUMP_PATH', 'mysqldump');
        $user = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');
        $db = env('DB_DATABASE');

        $filename ="backup-{$db}-" . now()->format('Y-m-d-H-i-s') . ".sql";

        // $command = "".env('DB_DUMP_PATH', 'mysqldump')." --user=" . env('DB_USERNAME') ." --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . "  | gzip > " . $storageAt . $filename;

        $command = "$dbDumpPath --user='$user' --password='$password' --host='$host' $db > {$storageAt}{$filename}";
        $returnVar = NULL;
        $output = NULL;
        exec($command, $output, $returnVar);

        if(!$returnVar) {
            $this->info("Backup for database {$db} created successfully in {$storageAt}{$filename}");
        } else {
            $this->error("Database backup failed.");
        }
    }
}

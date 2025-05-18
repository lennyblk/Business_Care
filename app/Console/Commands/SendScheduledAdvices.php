<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdviceSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendScheduledAdvices extends Command
{
    protected $signature = 'advices:send-scheduled';
    protected $description = 'Envoie les conseils programmés pour aujourd\'hui';

    protected $maxRetries = 3;
    protected $retryDelay = 10; // seconds

    public function handle()
    {
        $logMessage = "[" . now() . "] Démarrage de la commande\n";
        file_put_contents('/var/www/html/storage/logs/scheduler/advice-scheduled.log', $logMessage, FILE_APPEND);

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                // Test database connection
                DB::connection()->getPdo();
                $this->info('Database connection successful');
                break;
            } catch (\Exception $e) {
                if ($attempt === $this->maxRetries) {
                    $this->error("Database connection failed after {$this->maxRetries} attempts: " . $e->getMessage());
                    return 1;
                }
                $this->warn("Connection attempt {$attempt} failed. Retrying in {$this->retryDelay} seconds...");
                sleep($this->retryDelay);
            }
        }

        $today = Carbon::today();
        
        try {
            $scheduledAdvices = AdviceSchedule::with('advice')
                ->where('scheduled_date', $today)
                ->where('is_sent', false)
                ->get();

            $logMessage = "[" . now() . "] Nombre de conseils trouvés : " . $scheduledAdvices->count() . "\n";
            file_put_contents('/var/www/html/storage/logs/scheduler/advice-scheduled.log', $logMessage, FILE_APPEND);

            foreach ($scheduledAdvices as $schedule) {
                try {
                    // Marquer comme envoyé
                    $schedule->update([
                        'is_sent' => true,
                        'sent_at' => now()
                    ]);

                    $this->info("Conseil {$schedule->advice->title} marqué comme envoyé");
                } catch (\Exception $e) {
                    $this->error("Erreur pour le conseil {$schedule->advice->title}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->error('Error fetching scheduled advices: ' . $e->getMessage());
            return 1;
        }
    }
}

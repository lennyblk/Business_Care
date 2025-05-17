<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdviceSchedule;
use Carbon\Carbon;

class SendScheduledAdvices extends Command
{
    protected $signature = 'advices:send-scheduled';
    protected $description = 'Envoie les conseils programmés pour aujourd\'hui';

    public function handle()
    {
        $today = Carbon::today();
        
        $scheduledAdvices = AdviceSchedule::with('advice')
            ->where('scheduled_date', $today)
            ->where('is_sent', false)
            ->get();

        foreach ($scheduledAdvices as $schedule) {
            try {
                // Logique d'envoi du conseil
                if ($schedule->target_audience === 'All') {
                    // Envoyer à tous les employés
                    $employees = \App\Models\Employee::all();
                } else {
                    // Filtrer les employés selon target_criteria
                    $employees = \App\Models\Employee::where($schedule->target_criteria)->get();
                }

                foreach ($employees as $employee) {
                    // Envoi de notification/email
                    \Notification::send($employee, new \App\Notifications\NewAdviceNotification($schedule->advice));
                }

                // Marquer comme envoyé
                $schedule->update([
                    'is_sent' => true,
                    'sent_at' => now()
                ]);

                $this->info("Conseil {$schedule->advice->title} envoyé avec succès");
            } catch (\Exception $e) {
                $this->error("Erreur lors de l'envoi du conseil {$schedule->advice->title}: " . $e->getMessage());
            }
        }
    }
}

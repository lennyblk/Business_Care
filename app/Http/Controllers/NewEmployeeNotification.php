<?php

namespace App\Notifications;

use App\Models\Employee;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewEmployeeNotification extends Notification
{
    use Queueable;
    
    protected $employee;
    protected $company;
    
    /**
     * Create a new notification instance.
     *
     * @param Employee $employee
     * @param Company $company
     * @return void
     */
    public function __construct(Employee $employee, Company $company)
    {
        $this->employee = $employee;
        $this->company = $company;
    }
    
    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    
    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouveau collaborateur ajouté - ' . $this->company->name)
            ->greeting('Bonjour,')
            ->line('Un nouveau collaborateur a été ajouté à la société ' . $this->company->name)
            ->line('Nom: ' . $this->employee->name)
            ->line('Email: ' . $this->employee->email)
            ->line('Poste: ' . $this->employee->position)
            ->action('Voir les détails', route('admin.salaries.show', $this->employee->id))
            ->line('Merci d\'utiliser notre application!');
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
            'message' => 'Un nouveau collaborateur a été ajouté: ' . $this->employee->name . ' (' . $this->company->name . ')',
        ];
    }
}
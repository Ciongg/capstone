<?php

namespace App\Livewire\SuperAdmin\UserList\Modal;

use Livewire\Component;
use App\Models\User;
use App\Models\InboxMessage;
use App\Services\AuditLogService;

class UserContactModal extends Component
{
    // Passed from parent (optional)
    public ?int $userId = null;

    // Form fields
    public string $recipient_email = '';
    public string $subject = '';
    public string $messageBody = '';

    // UI helper
    public string $recipientLabel = '';

    public function mount($userId = null): void
    {
        $this->userId = $userId ?: null;

        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                $this->recipientLabel = "{$user->name} <{$user->email}>";
            } else {
                // If user no longer exists, clear selection so email becomes required
                $this->userId = null;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'recipient_email' => [$this->userId ? 'nullable' : 'required', 'email', 'exists:users,email'],
            'subject' => ['required', 'string', 'max:255'],
            'messageBody' => ['required', 'string', 'max:4000'],
        ];
    }

    protected array $validationAttributes = [
        'messageBody' => 'message',
    ];

    public function updated($prop): void
    {
        $this->validateOnly($prop);
    }

    public function sendMessage(): void
    {
        $this->validate();

        // Resolve recipient from email override or selected user
        $recipient = null;

        if (!empty($this->recipient_email)) {
            $recipient = User::where('email', $this->recipient_email)->first();
        } elseif (!empty($this->userId)) {
            $recipient = User::find($this->userId);
        }

        if (!$recipient) {
            $this->addError('recipient_email', 'Recipient not found.');
            return;
        }

        InboxMessage::create([
            'recipient_id' => $recipient->id,
            'subject' => $this->subject,
            'message' => $this->messageBody,
            'read_at' => null,
        ]);

        // Audit log the message sent
        AuditLogService::log(
            eventType: 'user_contacted',
            message: "Sent message to user: {$recipient->email}",
            resourceType: 'User',
            resourceId: $recipient->id,
            meta: [
                'recipient_name' => $recipient->name,
                'recipient_email' => $recipient->email,
                'subject' => $this->subject,
                'message_length' => strlen($this->messageBody),
            ]
        );

        // Reset form fields and show success message
        $this->resetForm();
        session()->flash('modal_message', 'Message sent successfully.');
    }

    private function resetForm(): void
    {
        $this->recipient_email = '';
        $this->subject = '';
        $this->messageBody = '';
    }

    public function render()
    {
        return view('livewire.super-admin.user-list.modal.user-contact-modal');
    }
}

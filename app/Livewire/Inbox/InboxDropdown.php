<?php

namespace App\Livewire\Inbox;

use Livewire\Component;
use App\Models\InboxMessage;
use Illuminate\Support\Facades\Auth;

class InboxDropdown extends Component
{
    public $messages = [];
    public $unreadCount = 0;
    public $mobileMode = false; // New property for mobile mode
    
    protected $listeners = [
        'refreshInbox' => '$refresh'
    ];
    
    public function mount($mobileMode = false)
    {
        $this->mobileMode = $mobileMode;
        $this->loadMessages();
    }
    
    public function loadMessages()
    {
        if (Auth::check()) {
            $this->messages = InboxMessage::where('recipient_id', Auth::id())
                ->latest()
                ->take(5)
                ->get();
                
            $this->unreadCount = InboxMessage::where('recipient_id', Auth::id())
                ->where('read_at', null)
                ->count();
        }
    }
    
    public function markAsRead($messageId)
    {
        $message = InboxMessage::where('id', $messageId)
            ->where('recipient_id', Auth::id())
            ->first();
            
        if ($message && !$message->read_at) {
            $message->read_at = now();
            $message->save();
            $this->loadMessages();
        }
    }
    
    public function markAllAsRead()
    {
        InboxMessage::where('recipient_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        $this->loadMessages();
    }
    
    public function render()
    {
        return view('livewire.inbox.inbox-dropdown');
    }
}

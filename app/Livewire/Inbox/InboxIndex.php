<?php

namespace App\Livewire\Inbox;

use App\Models\InboxMessage;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class InboxIndex extends Component
{
    use WithPagination;
    
    public $filter = 'all';
    public $hasAnyMessages = false; // Add this property to track if inbox has any messages
    
    protected $listeners = [
        'refreshInbox' => '$refresh'
    ];
    
    public function filterMessages($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }
    
    public function markAsRead($messageId)
    {
        $message = InboxMessage::where('id', $messageId)
            ->where('recipient_id', Auth::id())
            ->first();
            
        if ($message && !$message->read_at) {
            $message->read_at = \App\Services\TestTimeService::now();
            $message->save();
        }
    }
    
    public function markAllAsRead()
    {
        InboxMessage::where('recipient_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => \App\Services\TestTimeService::now()]);
    }
    
    public function clearInbox()
    {
        InboxMessage::where('recipient_id', Auth::id())->delete();
    }
    
    public function render()
    {
        $query = InboxMessage::where('recipient_id', Auth::id());
        
        // Check if there are any messages at all, regardless of filter
        $this->hasAnyMessages = InboxMessage::where('recipient_id', Auth::id())->exists();
        
        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }
        
        $messages = $query->latest()->paginate(15);
        
        $unreadCount = InboxMessage::where('recipient_id', Auth::id())
            ->whereNull('read_at')
            ->count();
            
        return view('livewire.inbox.inbox-index', compact('messages', 'unreadCount'));
    }
}

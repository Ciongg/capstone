<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxMessage extends Model
{
    use HasFactory;
    
    protected $fillable = [

        'recipient_id',
        'subject',
        'message',
        'read_at',
        'url',
    ];
    
    protected $casts = [
        'read_at' => 'datetime',
    ];
    

    
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
    
    public function isUnread()
    {
        return $this->read_at === null;
    }
}

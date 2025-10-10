<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;

class InboxMessage extends Model
{
    use HasFactory, HasUuid;
    
    protected $fillable = [
        'uuid',
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
    
}

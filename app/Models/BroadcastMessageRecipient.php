<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BroadcastMessageRecipient extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function broadcastMessage()
    {
        return $this->belongsTo(BroadcastMessage::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}

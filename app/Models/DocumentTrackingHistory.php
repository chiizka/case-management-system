<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTrackingHistory extends Model
{
    use HasFactory;

    protected $table = 'document_tracking_history';

    protected $fillable = [
        'document_tracking_id',
        'from_role',
        'to_role',
        'transferred_by_user_id',
        'transferred_at',
        'received_by_user_id',
        'received_at',
        'notes'
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'received_at' => 'datetime'
    ];

    public function documentTracking()
    {
        return $this->belongsTo(DocumentTracking::class);
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
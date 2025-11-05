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
        'location',
        'received_by',
        'date_received',
        'notes'
    ];

    protected $casts = [
        'date_received' => 'date'
    ];

    public function documentTracking()
    {
        return $this->belongsTo(DocumentTracking::class);
    }
}
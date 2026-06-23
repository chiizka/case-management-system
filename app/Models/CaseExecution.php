<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseExecution extends Model
{
    protected $table = 'case_executions';
    protected $fillable = [
        'case_id',
        'received_by',
        'date_received',
        'tracking_no',
        'courier',
        'forwarded_by',
        'notes',
    ];

    protected $casts = [
        'date_received' => 'datetime',
    ];

    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function forwardedBy()
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }
}
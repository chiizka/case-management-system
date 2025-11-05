<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTracking extends Model
{
    use HasFactory;

    protected $table = 'document_tracking';

    protected $fillable = [
        'case_id',
        'current_location',
        'received_by',
        'date_received',
        'status',
        'notes'
    ];

    protected $casts = [
        'date_received' => 'date'
    ];

    // ✅ UPDATED: Use CaseFile instead of Cases
    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    // Relationship to History
    public function history()
    {
        return $this->hasMany(DocumentTrackingHistory::class)->orderBy('date_received', 'desc');
    }
}
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
        'current_role',
        'status',
        'transferred_by_user_id',
        'transferred_at',
        'received_by_user_id',
        'received_at',
        'transfer_notes'
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'received_at' => 'datetime'
    ];

    // Role display names
    const ROLE_NAMES = [
        'admin' => 'Admin',
        'malsu' => 'MALSU',
        'case_management' => 'Case Management',
        'province' => 'Province'
    ];

    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function history()
    {
        return $this->hasMany(DocumentTrackingHistory::class)->orderBy('created_at', 'desc');
    }

    public function getRoleDisplayName()
    {
        return self::ROLE_NAMES[$this->current_role] ?? $this->current_role;
    }
}
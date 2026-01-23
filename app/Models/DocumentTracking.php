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

    const ROLE_NAMES = [
        'admin' => 'Admin',
        'malsu' => 'MALSU',
        'case_management' => 'Case Management',
        'records' => 'Records',
        
        // Province roles
        'province_albay' => 'Albay Province',
        'province_camarines_sur' => 'Camarines Sur Province',
        'province_camarines_norte' => 'Camarines Norte Province',
        'province_catanduanes' => 'Catanduanes Province',
        'province_masbate' => 'Masbate Province',
        'province_sorsogon' => 'Sorsogon Province',
        
        // Legacy support (if any old records still use 'province')
        'province' => 'Province (Legacy)',
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
        return self::ROLE_NAMES[$this->current_role] ?? ucfirst(str_replace('_', ' ', $this->current_role));
    }

    /**
     * Scope to filter out completed/dismissed cases
     */
    public function scopeActive($query)
    {
        return $query->whereHas('case', function($q) {
            $q->where('overall_status', 'Active');
        });
    }

    /**
     * Check if current role is a province role
     */
    public function isProvinceRole()
    {
        return in_array($this->current_role, [
            'province_albay',
            'province_camarines_sur',
            'province_camarines_norte',
            'province_catanduanes',
            'province_masbate',
            'province_sorsogon',
        ]);
    }

    /**
     * Get province name if it's a province role
     */
    public function getProvinceName()
    {
        if (!$this->isProvinceRole()) {
            return null;
        }

        $provinceNames = [
            'province_albay' => 'Albay',
            'province_camarines_sur' => 'Camarines Sur',
            'province_camarines_norte' => 'Camarines Norte',
            'province_catanduanes' => 'Catanduanes',
            'province_masbate' => 'Masbate',
            'province_sorsogon' => 'Sorsogon',
        ];

        return $provinceNames[$this->current_role] ?? null;
    }
}
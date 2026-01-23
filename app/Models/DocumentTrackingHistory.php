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

    /**
     * Get the display name for the 'from' role
     */
    public function getFromRoleDisplayName()
    {
        if (!$this->from_role) {
            return 'Initial';
        }
        return DocumentTracking::ROLE_NAMES[$this->from_role] ?? ucfirst(str_replace('_', ' ', $this->from_role));
    }

    /**
     * Get the display name for the 'to' role
     */
    public function getToRoleDisplayName()
    {
        return DocumentTracking::ROLE_NAMES[$this->to_role] ?? ucfirst(str_replace('_', ' ', $this->to_role));
    }

    /**
     * Check if the 'from_role' is a province role
     */
    public function isFromProvinceRole()
    {
        if (!$this->from_role) {
            return false;
        }
        
        return in_array($this->from_role, [
            'province_albay',
            'province_camarines_sur',
            'province_camarines_norte',
            'province_catanduanes',
            'province_masbate',
            'province_sorsogon',
        ]);
    }

    /**
     * Check if the 'to_role' is a province role
     */
    public function isToProvinceRole()
    {
        return in_array($this->to_role, [
            'province_albay',
            'province_camarines_sur',
            'province_camarines_norte',
            'province_catanduanes',
            'province_masbate',
            'province_sorsogon',
        ]);
    }

    /**
     * Get province name from 'from_role' if it's a province
     */
    public function getFromProvinceName()
    {
        if (!$this->isFromProvinceRole()) {
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

        return $provinceNames[$this->from_role] ?? null;
    }

    /**
     * Get province name from 'to_role' if it's a province
     */
    public function getToProvinceName()
    {
        if (!$this->isToProvinceRole()) {
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

        return $provinceNames[$this->to_role] ?? null;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppealsAndResolution extends Model
{
    use HasFactory;

    protected $table = 'appeals_and_resolution';

    protected $fillable = [
        'case_id',
        'date_returned_case_mgmt',
        'review_ct_cnpc',
        'date_received_drafter_finalization_2nd',
        'date_returned_case_mgmt_signature_2nd',
        'date_order_2nd_cnpc',
        'released_date_2nd_cnpc',
        'date_forwarded_malsu',
        'motion_reconsideration_date',
        'date_received_malsu',
        'date_resolution_mr',
        'released_date_resolution_mr',
        'date_appeal_received_records',
    ];

    protected $casts = [
        'date_returned_case_mgmt' => 'date',
        'date_received_drafter_finalization_2nd' => 'date',
        'date_returned_case_mgmt_signature_2nd' => 'date',
        'date_order_2nd_cnpc' => 'date',
        'released_date_2nd_cnpc' => 'date',
        'date_forwarded_malsu' => 'date',
        'motion_reconsideration_date' => 'date',
        'date_received_malsu' => 'date',
        'date_resolution_mr' => 'date',
        'released_date_resolution_mr' => 'date',
        'date_appeal_received_records' => 'date',
    ];

    /**
     * Get the case that owns the appeals and resolution record.
     */
    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    // Optional: Add accessor methods for convenience
    public function getInspectionIdAttribute()
    {
        return $this->case ? $this->case->inspection_id : null;
    }

    public function getEstablishmentNameAttribute()
    {
        return $this->case ? $this->case->establishment_name : null;
    }
}
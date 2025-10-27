<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAndDisposition extends Model
{
    protected $table = 'order_and_disposition';

    protected $fillable = [
        'case_id',
        'aging_2_days_finalization',  // ← ADD THIS
        'status_finalization',
        'pct_96_days',                // ← ADD THIS
        'date_signed_mis',
        'status_pct',
        'reference_date_pct',
        'aging_pct',                  // ← ADD THIS (if you use it)
        'disposition_mis',
        'disposition_actual',
        'findings_to_comply',
        'date_of_order_actual',
        'released_date_actual',
    ];

    protected $casts = [
        'date_signed_mis' => 'date',
        'reference_date_pct' => 'date',
        'date_of_order_actual' => 'date',
        'released_date_actual' => 'date',
        'aging_2_days_finalization' => 'integer',  // ← ADD THIS for proper type casting
        'pct_96_days' => 'integer',                 // ← ADD THIS for proper type casting
        'aging_pct' => 'integer',                   // ← ADD THIS if you use it
    ];

    // Define the belongs-to relationship with CaseFile
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
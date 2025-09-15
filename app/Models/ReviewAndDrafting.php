<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewAndDrafting extends Model
{
    protected $table = 'review_and_drafting'; 

    protected $fillable = [
        'case_id',
        'draft_order_type',
        'applicable_draft_order',
        'po_pct',
        'aging_po_pct',
        'status_po_pct',
        'date_received_from_po',
        'reviewer_drafter',              // now just a string
        'date_received_by_reviewer',
        'date_returned_from_drafter',
        'aging_10_days_tssd',
        'status_reviewer_drafter',
        'draft_order_tssd_reviewer',    // now just a string
    ];

    protected $casts = [
        'date_received_from_po' => 'date',
        'date_received_by_reviewer' => 'date',
        'date_returned_from_drafter' => 'date',
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

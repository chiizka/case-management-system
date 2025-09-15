<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAndDisposition extends Model
{
    protected $table = 'order_and_disposition';

    protected $fillable = [
        'case_id',
        'status_finalization',
        'date_signed_mis',
        'status_pct',
        'reference_date_pct',
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
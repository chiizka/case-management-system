<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $table = 'inspections';

    protected $fillable = [
        'case_id',
        'po_office',
        'inspector_name',
        'inspector_authority_no',
        'date_of_inspection',
        'date_of_nr',
        'lapse_20_day_period',
        'twg_ali',
    ];

    protected $casts = [
        'date_of_inspection' => 'date',
        'date_of_nr' => 'date',
        'lapse_20_day_period' => 'date',
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
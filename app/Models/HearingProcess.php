<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HearingProcess extends Model
{
    use HasFactory;

    protected $table = 'hearing_process';

    protected $fillable = [
        'case_id',
        'date_1st_mc_actual',
        'first_mc_pct',
        'status_1st_mc',
        'date_2nd_last_mc',
        'second_last_mc_pct',
        'status_2nd_mc',
        'case_folder_forwarded_to_ro',
        'complete_case_folder',
    ];

    protected $casts = [
        'date_1st_mc_actual' => 'date',
        'date_2nd_last_mc' => 'date',
        // Remove casting for PCT fields - let them be handled as strings
    ];

    /**
     * Get the case that owns the hearing process.
     */
    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function getInspectionIdAttribute()
    {
        return $this->case ? $this->case->inspection_id : null;
    }

    public function getEstablishmentAttribute()
    {
        return $this->case ? $this->case->establishment_name : null;
    }
}
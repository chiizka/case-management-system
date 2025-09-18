<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    // Add mutators to handle date formatting when saving
    public function setDateOfInspectionAttribute($value)
    {
        $this->attributes['date_of_inspection'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function setDateOfNrAttribute($value)
    {
        $this->attributes['date_of_nr'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function setLapse20DayPeriodAttribute($value)
    {
        $this->attributes['lapse_20_day_period'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    // Add accessors to format dates when retrieving
    public function getDateOfInspectionAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getDateOfNrAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getLapse20DayPeriodAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }
}
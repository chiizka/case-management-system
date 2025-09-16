<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseFile extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'inspection_id',
        'case_no',
        'establishment_name',
        'current_stage',
        'overall_status',
    ];

    protected $casts = [
        'current_stage' => 'string',
        'overall_status' => 'string',
    ];

    // ADD THIS METHOD
    protected static function booted()
    {
        static::created(function ($case) {
            // Automatically create an inspection record when a case is created
            $case->inspections()->create([
                'case_id' => $case->id,
                // All other fields will be null as per your migration
            ]);
        });
    }

    // Define the one-to-many relationship with inspections
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'case_id');
    }

    // Define the one-to-many relationship with docketing
    public function docketing()
    {
        return $this->hasMany(Docketing::class, 'case_id');
    }

    public function hearing_process(){
        return $this->hasMany(HearingProcess::class, 'case_id');
    }
}
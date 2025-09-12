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
        'current_stage' => 'string', // Fixed: enum values are strings
        'overall_status' => 'string',
    ];

    // Define the one-to-many relationship with inspections
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'case_id');
    }

    // Define the one-to-many relationship with docketing (use hasOne if one docketing per case)
    public function docketing()
    {
        return $this->hasMany(Docketing::class, 'case_id'); // or hasOne(Docketing::class, 'case_id')
    }

    public function hearing_process(){
        return $this->hasMany(HearingProcess::class, 'case_id'); // or hasOne(Docketing::class, 'case_id')
    }
}
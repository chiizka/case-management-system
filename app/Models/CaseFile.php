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
        'current_stage' => 'integer',
        'overall_status' => 'string',
    ];

    // Define the one-to-many relationship with inspections
    public function inspections()
    {
        return $this->hasMany(inspection::class, 'case_id');
    }
}
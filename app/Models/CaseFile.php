<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseFile extends Model
{
    protected $table = 'cases'; 
    
    protected $fillable = [
        'case_number',
        'case_status', 
        'case_type',
        'complainant',
        'respondent',
        'case_details',
        'date_filed',
    ];
}
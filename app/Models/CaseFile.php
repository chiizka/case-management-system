<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseFile extends Model
{
    use HasFactory;

    protected $table = 'cases';
    
    protected $fillable = [
        'inspection_id',
        'case_no', 
        'establishment_name',
        'current_stage',
        'overall_status'
    ];

    // MISSING RELATIONSHIP - This is what was causing the error
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'case_id');
    }

    // Existing relationships
    public function docketing()
    {
        return $this->hasMany(Docketing::class, 'case_id');
    }

    public function hearingProcesses()
    {
        return $this->hasMany(HearingProcess::class, 'case_id');
    }

    public function reviewAndDrafting()
    {
        return $this->hasMany(ReviewAndDrafting::class, 'case_id');
    }

    public function ordersAndDisposition()
    {
        return $this->hasMany(OrderAndDisposition::class, 'case_id');
    }

    public function complianceAndAwards()
    {
        return $this->hasMany(ComplianceAndAward::class, 'case_id');
    }

    public function appealsAndResolutions()
    {
        return $this->hasMany(AppealsAndResolution::class, 'case_id');
    }

    // ✅ ADD THIS NEW RELATIONSHIP
    public function documentTracking()
    {
        return $this->hasOne(DocumentTracking::class, 'case_id');
    }
}
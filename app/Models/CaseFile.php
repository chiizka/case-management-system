<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $inspection_id
 * @property string|null $case_no
 * @property string $establishment_name
 * @property string $current_stage
 * @property string $overall_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AppealsAndResolution> $appealsAndResolutions
 * @property-read int|null $appeals_and_resolutions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ComplianceAndAward> $complianceAndAwards
 * @property-read int|null $compliance_and_awards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\docketing> $docketing
 * @property-read int|null $docketing_count
 * @property-read \App\Models\DocumentTracking|null $documentTracking
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HearingProcess> $hearingProcesses
 * @property-read int|null $hearing_processes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inspection> $inspections
 * @property-read int|null $inspections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderAndDisposition> $ordersAndDisposition
 * @property-read int|null $orders_and_disposition_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReviewAndDrafting> $reviewAndDrafting
 * @property-read int|null $review_and_drafting_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereCaseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereCurrentStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereEstablishmentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereInspectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereOverallStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $case_id
 * @property string|null $pct_for_docketing
 * @property string|null $date_scheduled_docketed
 * @property int|null $aging_docket
 * @property string|null $status_docket
 * @property string|null $hearing_officer_mis
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereAgingDocket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereDateScheduledDocketed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereHearingOfficerMis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing wherePctForDocketing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereStatusDocket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|docketing whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class docketing extends Model
{
    protected $table = 'docketing';

    protected $fillable = [
        'case_id',
        'pct_for_docketing',
        'date_scheduled_docketed',
        'aging_docket',
        'status_docket',
        'hearing_officer_mis'
    ];

    public function case(){
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function getInspectionIdAttribute(){
        return $this->case ? $this->case->inspection_id : null;
    }

    public function getEstablishmentAttribute(){
        return $this->case ? $this->case->establishment_name : null; // FIXED: was returning inspection_id
    }
}
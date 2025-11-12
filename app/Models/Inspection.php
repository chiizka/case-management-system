<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $case_id
 * @property string|null $po_office
 * @property string|null $inspector_name
 * @property string|null $inspector_authority_no
 * @property string|null $date_of_inspection
 * @property string|null $date_of_nr
 * @property string|null $twg_ali
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $lapse_20_day_period
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @property mixed $lapse20_day_period
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereDateOfInspection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereDateOfNr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereInspectorAuthorityNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereInspectorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereLapse20DayPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection wherePoOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereTwgAli($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inspection whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
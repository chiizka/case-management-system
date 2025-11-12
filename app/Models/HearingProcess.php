<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $case_id
 * @property \Illuminate\Support\Carbon|null $date_1st_mc_actual
 * @property string|null $first_mc_pct
 * @property string|null $status_1st_mc
 * @property \Illuminate\Support\Carbon|null $date_2nd_last_mc
 * @property string|null $second_last_mc_pct
 * @property string|null $status_2nd_mc
 * @property string|null $case_folder_forwarded_to_ro
 * @property string|null $complete_case_folder
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereCaseFolderForwardedToRo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereCompleteCaseFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereDate1stMcActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereDate2ndLastMc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereFirstMcPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereSecondLastMcPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereStatus1stMc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereStatus2ndMc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HearingProcess whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
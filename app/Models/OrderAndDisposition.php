<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $case_id
 * @property int|null $aging_2_days_finalization Calculated field
 * @property string|null $status_finalization
 * @property int|null $pct_96_days Calculated field
 * @property \Illuminate\Support\Carbon|null $date_signed_mis
 * @property string|null $status_pct
 * @property \Illuminate\Support\Carbon|null $reference_date_pct
 * @property int|null $aging_pct Calculated field
 * @property string|null $disposition_mis
 * @property string|null $disposition_actual
 * @property string|null $findings_to_comply
 * @property \Illuminate\Support\Carbon|null $date_of_order_actual
 * @property \Illuminate\Support\Carbon|null $released_date_actual
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereAging2DaysFinalization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereAgingPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereDateOfOrderActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereDateSignedMis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereDispositionActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereDispositionMis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereFindingsToComply($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition wherePct96Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereReferenceDatePct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereReleasedDateActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereStatusFinalization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereStatusPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderAndDisposition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderAndDisposition extends Model
{
    protected $table = 'order_and_disposition';

    protected $fillable = [
        'case_id',
        'aging_2_days_finalization',  // ← ADD THIS
        'status_finalization',
        'pct_96_days',                // ← ADD THIS
        'date_signed_mis',
        'status_pct',
        'reference_date_pct',
        'aging_pct',                  // ← ADD THIS (if you use it)
        'disposition_mis',
        'disposition_actual',
        'findings_to_comply',
        'date_of_order_actual',
        'released_date_actual',
    ];

    protected $casts = [
        'date_signed_mis' => 'date',
        'reference_date_pct' => 'date',
        'date_of_order_actual' => 'date',
        'released_date_actual' => 'date',
        'aging_2_days_finalization' => 'integer',  // ← ADD THIS for proper type casting
        'pct_96_days' => 'integer',                 // ← ADD THIS for proper type casting
        'aging_pct' => 'integer',                   // ← ADD THIS if you use it
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
}
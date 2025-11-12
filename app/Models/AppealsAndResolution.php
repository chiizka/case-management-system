<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $case_id
 * @property \Illuminate\Support\Carbon|null $date_returned_case_mgmt
 * @property string|null $review_ct_cnpc
 * @property \Illuminate\Support\Carbon|null $date_received_drafter_finalization_2nd
 * @property \Illuminate\Support\Carbon|null $date_returned_case_mgmt_signature_2nd
 * @property \Illuminate\Support\Carbon|null $date_order_2nd_cnpc
 * @property \Illuminate\Support\Carbon|null $released_date_2nd_cnpc
 * @property \Illuminate\Support\Carbon|null $date_forwarded_malsu
 * @property \Illuminate\Support\Carbon|null $motion_reconsideration_date
 * @property \Illuminate\Support\Carbon|null $date_received_malsu
 * @property \Illuminate\Support\Carbon|null $date_resolution_mr
 * @property \Illuminate\Support\Carbon|null $released_date_resolution_mr
 * @property \Illuminate\Support\Carbon|null $date_appeal_received_records
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateAppealReceivedRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateForwardedMalsu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateOrder2ndCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateReceivedDrafterFinalization2nd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateReceivedMalsu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateResolutionMr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateReturnedCaseMgmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereDateReturnedCaseMgmtSignature2nd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereMotionReconsiderationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereReleasedDate2ndCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereReleasedDateResolutionMr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereReviewCtCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppealsAndResolution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AppealsAndResolution extends Model
{
    use HasFactory;

    protected $table = 'appeals_and_resolution';

    protected $fillable = [
        'case_id',
        'date_returned_case_mgmt',
        'review_ct_cnpc',
        'date_received_drafter_finalization_2nd',
        'date_returned_case_mgmt_signature_2nd',
        'date_order_2nd_cnpc',
        'released_date_2nd_cnpc',
        'date_forwarded_malsu',
        'motion_reconsideration_date',
        'date_received_malsu',
        'date_resolution_mr',
        'released_date_resolution_mr',
        'date_appeal_received_records',
    ];

    protected $casts = [
        'date_returned_case_mgmt' => 'date',
        'date_received_drafter_finalization_2nd' => 'date',
        'date_returned_case_mgmt_signature_2nd' => 'date',
        'date_order_2nd_cnpc' => 'date',
        'released_date_2nd_cnpc' => 'date',
        'date_forwarded_malsu' => 'date',
        'motion_reconsideration_date' => 'date',
        'date_received_malsu' => 'date',
        'date_resolution_mr' => 'date',
        'released_date_resolution_mr' => 'date',
        'date_appeal_received_records' => 'date',
    ];

    /**
     * Get the case that owns the appeals and resolution record.
     */
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
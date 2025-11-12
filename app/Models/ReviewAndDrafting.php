<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $case_id
 * @property string|null $draft_order_type
 * @property string $applicable_draft_order
 * @property int|null $po_pct
 * @property int|null $aging_po_pct
 * @property string $status_po_pct
 * @property \Illuminate\Support\Carbon|null $date_received_from_po
 * @property string|null $reviewer_drafter
 * @property \Illuminate\Support\Carbon|null $date_received_by_reviewer
 * @property \Illuminate\Support\Carbon|null $date_returned_from_drafter
 * @property int|null $aging_10_days_tssd
 * @property string $status_reviewer_drafter
 * @property string|null $draft_order_tssd_reviewer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereAging10DaysTssd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereAgingPoPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereApplicableDraftOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDateReceivedByReviewer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDateReceivedFromPo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDateReturnedFromDrafter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDraftOrderTssdReviewer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereDraftOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting wherePoPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereReviewerDrafter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereStatusPoPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereStatusReviewerDrafter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewAndDrafting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReviewAndDrafting extends Model
{
    protected $table = 'review_and_drafting'; 

    protected $fillable = [
        'case_id',
        'draft_order_type',
        'applicable_draft_order',
        'po_pct',
        'aging_po_pct',
        'status_po_pct',
        'date_received_from_po',
        'reviewer_drafter',
        'date_received_by_reviewer',
        'date_returned_from_drafter',
        'aging_10_days_tssd',
        'status_reviewer_drafter',
        'draft_order_tssd_reviewer',
    ];

    protected $casts = [
        'date_received_from_po' => 'date',
        'date_received_by_reviewer' => 'date',
        'date_returned_from_drafter' => 'date',
        'po_pct' => 'integer',
        'aging_po_pct' => 'integer',
        'aging_10_days_tssd' => 'integer',
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
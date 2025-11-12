<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $case_id
 * @property string|null $compliance_order_monetary_award
 * @property string|null $osh_penalty
 * @property int|null $affected_male
 * @property int|null $affected_female
 * @property int $first_order_dismissal_cnpc
 * @property int $tavable_less_than_10_workers
 * @property int $with_deposited_monetary_claims
 * @property string|null $amount_deposited
 * @property int $with_order_payment_notice
 * @property string|null $status_all_employees_received
 * @property string|null $status_case_after_first_order
 * @property string|null $date_notice_finality_dismissed
 * @property string|null $released_date_notice_finality
 * @property int $updated_ticked_in_mis
 * @property string|null $second_order_drafter
 * @property string|null $date_received_by_drafter_ct_cnpc
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read mixed $establishment_name
 * @property-read mixed $inspection_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereAffectedFemale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereAffectedMale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereAmountDeposited($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereComplianceOrderMonetaryAward($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereDateNoticeFinalityDismissed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereDateReceivedByDrafterCtCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereFirstOrderDismissalCnpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereOshPenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereReleasedDateNoticeFinality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereSecondOrderDrafter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereStatusAllEmployeesReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereStatusCaseAfterFirstOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereTavableLessThan10Workers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereUpdatedTickedInMis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereWithDepositedMonetaryClaims($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComplianceAndAward whereWithOrderPaymentNotice($value)
 * @mixin \Eloquent
 */
class ComplianceAndAward extends Model
{
    use HasFactory;

    protected $table = 'compliance_and_awards';

    protected $fillable = [
        'case_id',
        'compliance_order_monetary_award',
        'osh_penalty',
        'affected_male',
        'affected_female',
        'first_order_dismissal_cnpc',
        'tavable_less_than_10_workers',
        'with_deposited_monetary_claims',
        'amount_deposited',
        'with_order_payment_notice',
        'status_all_employees_received',
        'status_case_after_first_order',
        'date_notice_finality_dismissed',
        'released_date_notice_finality',
        'updated_ticked_in_mis',
        'second_order_drafter',
        'date_received_by_drafter_ct_cnpc',
    ];

    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

     public function getInspectionIdAttribute()
    {
        return $this->case ? $this->case->inspection_id : null;
    }

    public function getEstablishmentNameAttribute()
    {
        return $this->case ? $this->case->establishment_name : null;
    }
}

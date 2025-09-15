<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

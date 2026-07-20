<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Malsu extends Model
{
    protected $table = 'malsu';

    protected $fillable = [
        'case_id',
        'case_title',
        'regional_docket_number',
        'sheriff_designate',
        'date_compliance_order',
        'voluntary_compliance',
        'action_taken',
        'full_or_partial',
        'total_gls_monetary_award',
        'total_workers_benefited',
        'amount_penalty_double_indemnity',
        'total_gls_monetary_satisfied',
        'total_workers_satisfied',
        'total_workers_absorbed',
        'complied_oshs_violations',
        'total_penalty_double_indemnity_collected',
        'total_oshs_penalty_admin_fines_collected',
        'date_writ_of_execution_served',
        'date_indorsed_to_po',
        'po_date_received',
        'ro_received_sheriffs_return',
    ];

    protected $casts = [
        'date_compliance_order'            => 'date',
        'date_writ_of_execution_served'    => 'date',
        'date_indorsed_to_po'              => 'date',
        'po_date_received'                 => 'date',
        'ro_received_sheriffs_return'      => 'date',
        'total_gls_monetary_award'         => 'decimal:2',
        'amount_penalty_double_indemnity'  => 'decimal:2',
        'total_gls_monetary_satisfied'     => 'decimal:2',
        'total_penalty_double_indemnity_collected' => 'decimal:2',
        'total_oshs_penalty_admin_fines_collected' => 'decimal:2',
    ];

    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function sheriffsReports()
    {
        return $this->hasMany(\App\Models\SheriffsReport::class, 'malsu_id')
            ->orderBy('report_month', 'desc');
    }
}
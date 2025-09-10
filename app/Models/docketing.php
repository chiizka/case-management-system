<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
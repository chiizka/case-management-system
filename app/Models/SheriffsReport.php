<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheriffsReport extends Model
{
    use HasFactory;

    protected $table = 'malsu_sheriff_reports';

    protected $fillable = [
        'malsu_id',
        'report_month',
        'report_date_submitted',
        'report_content',
        'scanned_file_path',
        'report_link',
        'submitted_by_user_id',
    ];

    protected $casts = [
        'report_month'           => 'date:Y-m-d',
        'report_date_submitted'  => 'date:Y-m-d',
    ];

    public function malsu()
    {
        return $this->belongsTo(Malsu::class, 'malsu_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }
}
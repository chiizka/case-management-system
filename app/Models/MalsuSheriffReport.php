<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MalsuSheriffReport extends Model
{
    protected $table = 'malsu_sheriff_reports';

    protected $fillable = [
        'malsu_id',
        'report_month',
        'report_date_submitted',
        'report_content',
        'scanned_file_path',
    ];

    protected $casts = [
        'report_month'          => 'date',
        'report_date_submitted' => 'date',
    ];

    public function malsu()
    {
        return $this->belongsTo(Malsu::class, 'malsu_id');
    }
}
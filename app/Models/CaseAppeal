<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseAppeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'appellate_body',
        'transmittal_date',
        'destination',
        'notes',
    ];

    protected $casts = [
        'transmittal_date' => 'date',
    ];

    /**
     * Get the case that owns the appeal.
     */
    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }
}
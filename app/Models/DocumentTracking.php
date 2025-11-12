<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $case_id
 * @property string $current_role
 * @property string $status
 * @property int|null $transferred_by_user_id
 * @property \Illuminate\Support\Carbon|null $transferred_at
 * @property int|null $received_by_user_id
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property string|null $transfer_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CaseFile $case
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentTrackingHistory> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\User|null $receivedBy
 * @property-read \App\Models\User|null $transferredBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereCurrentRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereReceivedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereTransferNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereTransferredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereTransferredByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTracking whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DocumentTracking extends Model
{
    use HasFactory;

    protected $table = 'document_tracking';

    protected $fillable = [
        'case_id',
        'current_role',
        'status',
        'transferred_by_user_id',
        'transferred_at',
        'received_by_user_id',
        'received_at',
        'transfer_notes'
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'received_at' => 'datetime'
    ];

    const ROLE_NAMES = [
        'admin' => 'Admin',
        'malsu' => 'MALSU',
        'case_management' => 'Case Management',
        'province' => 'Province',
        'records' => 'Records',
    ];

    public function case()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function history()
    {
        return $this->hasMany(DocumentTrackingHistory::class)->orderBy('created_at', 'desc');
    }

    public function getRoleDisplayName()
    {
        return self::ROLE_NAMES[$this->current_role] ?? $this->current_role;
    }

    // Add scope to filter out completed/dismissed cases
    public function scopeActive($query)
    {
        return $query->whereHas('case', function($q) {
            $q->where('overall_status', 'Active');
        });
    }
}
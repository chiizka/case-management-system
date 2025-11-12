<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $document_tracking_id
 * @property string|null $from_role
 * @property string $to_role
 * @property int|null $transferred_by_user_id
 * @property \Illuminate\Support\Carbon|null $transferred_at
 * @property int|null $received_by_user_id
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DocumentTracking $documentTracking
 * @property-read \App\Models\User|null $receivedBy
 * @property-read \App\Models\User|null $transferredBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereDocumentTrackingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereFromRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereReceivedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereToRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereTransferredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereTransferredByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTrackingHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DocumentTrackingHistory extends Model
{
    use HasFactory;

    protected $table = 'document_tracking_history';

    protected $fillable = [
        'document_tracking_id',
        'from_role',
        'to_role',
        'transferred_by_user_id',
        'transferred_at',
        'received_by_user_id',
        'received_at',
        'notes'
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'received_at' => 'datetime'
    ];

    public function documentTracking()
    {
        return $this->belongsTo(DocumentTracking::class);
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
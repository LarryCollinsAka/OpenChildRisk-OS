<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertAction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'alert_id',
        'user_id',
        'action_type',
        'action_details',
        'taken_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'action_details' => 'array',
        'taken_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Alert this action belongs to
     */
    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    /**
     * User who took this action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
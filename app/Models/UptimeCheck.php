<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UptimeCheck extends Model
{
    protected $fillable = [
        'website_id',
        'is_up',
        'response_time_ms',
        'status_code',
        'failure_reason',
        'checked_at',
    ];

    protected $casts = [
        'is_up' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function scopeLastDay(Builder $q): Builder
    {
        return $q->where('checked_at', '>=', now()->subDay());
    }

    public function scopeLastDays(Builder $q, int $days): Builder
    {
        return $q->where('checked_at', '>=', now()->subDays($days));
    }

    public function scopeUp(Builder $q): Builder
    {
        return $q->where('is_up', true);
    }

    public function scopeDown(Builder $q): Builder
    {
        return $q->where('is_up', false);
    }
}

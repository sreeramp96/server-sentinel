<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = [
        'website_id',
        'started_at',
        'resolved_at',
        'duration_minutes',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'started_at'  => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function isOngoing(): bool
    {
        return $this->resolved_at === null;
    }

    public function durationLabel(): string
    {
        if ($this->isOngoing()) {
            $mins = $this->started_at->diffInMinutes(now());

            return $mins < 60
                ? "{$mins} min (ongoing)"
                : round($mins / 60, 1).' hr (ongoing)';
        }

        $mins = $this->duration_minutes ?? $this->started_at->diffInMinutes($this->resolved_at);

        return $mins < 60 ? "{$mins} min" : round($mins / 60, 1).' hr';
    }
}

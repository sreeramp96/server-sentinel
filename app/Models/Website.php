<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Website extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'is_active',
        'is_monitoring',
        'is_public',
        'public_slug',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_monitoring' => 'boolean',
            'is_public' => 'boolean',
            'last_notified_at' => 'datetime',
            'status_page_enabled' => 'boolean',
        ];
    }

    public function uptimeChecks(): HasMany
    {
        return $this->hasMany(UptimeCheck::class);
    }

    public function latestCheck(): HasOne
    {
        return $this->hasOne(UptimeCheck::class)->latestOfMany('checked_at');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class)->orderByDesc('started_at');
    }

    public function getUptimePctAttribute(): float
    {
        if (! isset($this->total_checks_count) || $this->total_checks_count === 0) {
            return 0.0;
        }

        return round(($this->up_checks_count / $this->total_checks_count) * 100, 2);
    }

    public function getUptime24hAttribute(): float
    {
        if (! isset($this->total_24h_count) || $this->total_24h_count === 0) {
            return 0.0;
        }

        return round(($this->up_24h_count / $this->total_24h_count) * 100, 2);
    }

    public function getAvgMsAttribute(): int
    {
        return (int) ($this->avg_ms_val ?? 0);
    }

    public function getDowntimeMinsAttribute(): int
    {
        return (int) ($this->downtime_mins_count ?? 0);
    }

    // Returns 0–100 based on last X days of checks
    public function uptimePercentage(int $days = 1): float
    {
        if ($days === 1 && isset($this->total_24h_count)) {
            return $this->uptime_24h;
        }

        $checks = $this->uptimeChecks()->lastDays($days)->get();

        if ($checks->isEmpty()) {
            return 0.0;
        }

        return round(
            $checks->where('is_up', true)->count() / $checks->count() * 100,
            2
        );
    }

    public function avgResponseTime(int $days = 1): ?int
    {
        return (int) $this->uptimeChecks()
            ->lastDays($days)
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');
    }

    public function responseTimeHistory(): array
    {
        return $this->uptimeChecks()
            ->lastDay()
            ->orderBy('checked_at')
            ->get(['checked_at', 'response_time_ms', 'is_up'])
            ->map(fn ($c) => [
                'time' => $c->checked_at->format('H:i'),
                'ms' => $c->response_time_ms,
                'is_up' => $c->is_up,
            ])
            ->toArray();
    }

    // Total downtime minutes in last 24h
    public function downtimeMinutes(): int
    {
        // Each check = 1 minute interval, so count of down checks ≈ minutes down
        return $this->uptimeChecks()->lastDay()->down()->count();
    }

    public function generatePublicSlug(): string
    {
        return Str::random(10);
    }
}

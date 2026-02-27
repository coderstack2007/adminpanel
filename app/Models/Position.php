<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['subdivision_id', 'name', 'category', 'grade', 'is_vacant'];

    protected $casts = [
        'is_vacant' => 'boolean',
    ];

    public function subdivision() { return $this->belongsTo(Subdivision::class); }
    public function users()       { return $this->hasMany(User::class); }

    public function getCategoryLabelAttribute(): string
    {
        return "Категория {$this->category}, {$this->grade}-й разряд";
    }

    /**
     * Пересчитать is_vacant на основе реальных данных.
     * Вызывай после создания/удаления пользователя на этой должности.
     */
    public function syncVacancy(): void
    {
        $hasUsers = $this->users()->exists();
        $this->update(['is_vacant' => !$hasUsers]);
    }
}
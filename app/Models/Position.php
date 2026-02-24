<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['subdivision_id', 'name', 'category', 'grade'];

    public function subdivision() { return $this->belongsTo(Subdivision::class); }
    public function users()       { return $this->hasMany(User::class); }

    public function getCategoryLabelAttribute(): string
    {
        return "Категория {$this->category}, {$this->grade}-й разряд";
    }
}
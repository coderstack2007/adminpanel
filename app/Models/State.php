<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['key', 'label_ru', 'color', 'order'];

    public function vacancyRequests()
    {
        return $this->hasMany(VacancyRequest::class);
    }

    // Удобный доступ по ключу: State::byKey('approved')
    public static function byKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }
}
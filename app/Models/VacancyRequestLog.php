<?php
// app/Models/VacancyRequestLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacancyRequestLog extends Model
{
    protected $fillable = ['vacancy_request_id', 'user_id', 'status', 'comment'];

    public function user()           { return $this->belongsTo(User::class); }
    public function vacancyRequest() { return $this->belongsTo(VacancyRequest::class); }

    public function getStatusLabelAttribute(): string
    {
        return VacancyRequest::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
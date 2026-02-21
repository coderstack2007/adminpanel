<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_code',
        'branch_id',
        'department_id',
        'subdivision_id',
        'position_id',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ─── Отношения ───────────────────────────────────────────────
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // ─── Хелперы ────────────────────────────────────────────────
    public function getPositionCategoryAttribute(): ?string
    {
        return $this->position?->category;
    }

    public function getPositionGradeAttribute(): ?int
    {
        return $this->position?->grade;
    }

    public function getDisplayRoleAttribute(): string
    {
        return $this->roles->first()?->name ?? 'Нет роли';
    }
}
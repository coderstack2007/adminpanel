<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdivision extends Model
{
    protected $fillable = ['department_id', 'name', 'code', 'head_user_id', 'is_active'];

    public function department() { return $this->belongsTo(Department::class); }
    public function positions()  { return $this->hasMany(Position::class); }
    public function head()       { return $this->belongsTo(User::class, 'head_user_id'); }
    public function users()      { return $this->hasMany(User::class); }
}
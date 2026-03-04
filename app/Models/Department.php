<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['branch_id', 'name', 'code', 'head_user_id', 'is_active'];

    public function branch()       { return $this->belongsTo(Branch::class); }
    public function subdivisions() { return $this->hasMany(Subdivision::class); }
    public function head()         { return $this->belongsTo(User::class, 'head_user_id'); }
    public function users()        { return $this->hasMany(User::class); }
}
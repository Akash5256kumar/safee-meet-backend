<?php

namespace App\Models;
use App\Models\Admin;


use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Admin-panel roles live in admin_roles, not the app's user-facing
    // "roles" table (different schema, unrelated feature).
    protected $table = 'admin_roles';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }
}

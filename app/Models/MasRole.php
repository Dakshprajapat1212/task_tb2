<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasRole extends Model
{
    use HasFactory;

    protected $table = 'mas_roles';

    protected $fillable = [
        'name'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Mas role has many users
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}

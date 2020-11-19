<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    protected $casts = [
        'update_time' => 'datetime:Y-m-d H:i:s',
        'create_time' => 'datetime:Y-m-d H:i:s',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{

    protected $fillable = [
        'id',
        'name',

    ];
    // make relation between department and libyan person request
    public function libyanPersonRequests()
    {
        return $this->hasMany(LibyanPersonRequest::class);
    }
}

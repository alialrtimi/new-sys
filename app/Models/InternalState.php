<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalState extends Model
{

    protected $fillable = [
        'id',
        'name',

    ];

    public function libyanPersonRequests()
    {
        return $this->hasMany(LibyanPersonRequest::class);
    }
}

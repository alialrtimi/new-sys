<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    public function libyanPerson()
    {
        return $this->hasMany(LibyanPerson::class);
    }
}

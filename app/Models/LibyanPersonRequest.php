<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibyanPersonRequest extends Model
{

    protected $table = 'libyan_people_requests';
    public function libyanPerson()
    {




        return $this->belongsTo(LibyanPerson::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);

        // return $this->hasOne(Department::class)->latestOfMany();
    }

    public function internalState()
    {
        // return $this->belongsTo(InternalState::class);

        return $this->hasOne(InternalState::class)->latestOfMany();
    }
}

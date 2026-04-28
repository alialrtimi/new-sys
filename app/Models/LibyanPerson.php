<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibyanPerson extends Model
{
    protected $table = 'libyan_people';
    //
    public function libyanPersonRequests()
    {
        // return $this->hasMany(LibyanPersonRequest::class);
        return $this->hasOne(LibyanPersonRequest::class)->latestOfMany();
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function document_issued_place()
    {
        return $this->belongsTo(Address::class, 'document_issue_place_id');
    }
}

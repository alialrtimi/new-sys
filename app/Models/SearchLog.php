<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $fillable = [
        'partial_case_number',
        'criminal_case_number',
        'name',
        'mother_name',
        'date_of_birth',
        'job',
        'address',
        'charges',
        'judgment_date',
        'judgment_operative',
        'inserted_way',
        'user_id',
        'result'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

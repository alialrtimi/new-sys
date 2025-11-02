<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;


class Person extends Model
{
    use SoftDeletes, LogsActivity;


    // $activities = Activity::all();

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
        'excel_upload_id',
        'note',
        'note_id',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable) // الحقول المراد تسجيلها
            ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}");
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function person_note()
    {
        return $this->belongsTo(Note::class, 'note_id');
    }
}

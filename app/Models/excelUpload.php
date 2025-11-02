<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class excelUpload extends Model
{

    use SoftDeletes;
    protected $table = 'excel_uploads';
}

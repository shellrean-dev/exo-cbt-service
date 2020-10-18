<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SesiSchedule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'peserta_ids'   => 'array'
    ];
}

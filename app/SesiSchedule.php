<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class SesiSchedule extends Model
{
    use Uuids;

    protected $guarded = [];

    protected $casts = [
        'peserta_ids'   => 'array'
    ];
}

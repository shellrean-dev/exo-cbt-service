<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];

    /**
     * [$casts description]
     * @var [type]
     */
    protected $casts = [
        'value' => 'array'
    ];  
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Jurusan extends Model
{
    use Uuids;

    /**
     * [$timestamps description]
     * @var boolean
     */
    public $timestamps = false;

    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];
}

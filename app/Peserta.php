<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];

    /**
     * [agama description]
     * @return [type] [description]
     */
    public function agama()
    {
        return $this->belongsTo(Agama::class);
    }

    /**
     * [jurusan description]
     * @return [type] [description]
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }
}

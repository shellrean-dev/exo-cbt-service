<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    protected $guarded = [];

    public function agama()
    {
        return $this->belongsTo(Agama::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JawabanEsay extends Model
{
    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];
    
    /**
     * [pertanyaan description]
     * @return [type] [description]
     */
    public function pertanyaan()
    {
        return $this->hasOne('App\Soal','id','soal_id');
    }
}

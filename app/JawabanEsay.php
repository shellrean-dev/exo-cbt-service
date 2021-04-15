<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class JawabanEsay extends Model
{
    use Uuids;

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

<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class PenilaianEsay extends Model
{
    use Uuids;

    protected $table = "penilaian_esay";

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

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Peserta extends Model
{
    use Uuids;

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

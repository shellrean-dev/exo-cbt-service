<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UjianAktif extends Model
{
    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];
    
    /**
     * [$fillable description]
     * @var [type]
     */
    protected $fillable = ['kelompok','ujian_id','token','status_token'];

    /**
     * [jadwal description]
     * @return [type] [description]
     */
    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class,'ujian_id','id');
    }
}

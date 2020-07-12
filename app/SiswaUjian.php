<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiswaUjian extends Model
{
    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];

    /**
     * [$appends description]
     * @var array
     */
    protected $appends = ['status'];

    /**
     * [peserta description]
     * @return [type] [description]
     */
    public function peserta() {
        return $this->hasOne('App\Peserta','id','peserta_id');
    }

    /**
     * [getStatusAttribute description]
     * @return [type] [description]
     */
    public function getStatusAttribute()
    {
        if($this->status_ujian == '0') {
            $res = 'Belum mulai';
        }
        elseif($this->status_ujian == '3') {
            $res = 'Sedang mengerjakan';
        }
        elseif($this->status_ujian == '1') {
            $res = 'Test selesai';
        }

        return $res;
    }

    /**
     * [hasil description]
     * @return [type] [description]
     */
    public function hasil()
    {
        return $this->hasOne('App\HasilUjian','peserta_id','peserta_id');
    }
}

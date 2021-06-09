<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class SiswaUjian extends Model
{
    use Uuids;

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
        switch(trim($this->status_ujian)) {
            case '0':
                $res = 'Belum mulai';
                break;
            case '3':
                $res = 'Sedang mengerjakan';
                break;
            case '1':
                $res = 'Test selesai';
                break;
            default:
                $res = 'Untrace';
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

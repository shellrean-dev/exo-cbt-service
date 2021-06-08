<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Peserta extends Model
{
    use Uuids;

    /**
     * protected unviewable property
     * @var array
     */
    protected $guarded = [];

    /**
     * Peserta's agama
     * @return object
     * @author shellrean <wandinak17@gmail.com>
     */
    public function agama()
    {
        return $this->belongsTo(Agama::class);
    }

    /**
     * Peserta's jurusan
     * @return object
     * @author shellrean <wandinak17@gmail.com>
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Group member
     * @return object
     * @author shellrean <wandinak17@gmail.com>
     */
    public function group()
    {
        return $this->belongsTo(GroupMember::class,'id', 'student_id');
    }
}

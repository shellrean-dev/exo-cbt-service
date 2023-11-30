<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use Illuminate\Support\Facades\DB;

class Matpel extends Model
{
    use Uuids;

    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];

    /**
     * [$appends description]
     * @var [type]
     */
    protected $appends = [ 'jurusans', 'correctors_name', 'agama' ];

    /**
     * [$hidden description]
     * @var [type]
     */
    protected $hidden = [ 'created_at', 'updated_at' ];

    /**
     * [$casts description]
     * @var [type]
     */
    protected $casts = [
        'jurusan_id'    => 'array',
        'correctors'    => 'array'
    ];

    /**
     * [getJurusansAttribute description]
     * @return [type] [description]
     */
    public function getJurusansAttribute()
    {
        if($this->jurusan_id != '0') {
            $jurusans = DB::table('jurusans')->whereIn('id', $this->jurusan_id)->get();
            return $jurusans;
        }
        return 0;
    }

    /**
     * [setJurusanIdAttribute description]
     */
    public function setJurusanIdAttribute($value)
    {
        if(is_array($value)) {
            $this->attributes['jurusan_id'] = json_encode($value);
        } else {
            $this->attributes['jurusan_id'] = str_replace('"', '', $value);
        }
    }

    /**
     * [setAgamaIdAttribute description]
     * @param [type] $value [description]
     */
    public function setAgamaIdAttribute($value)
    {
        $this->attributes['agama_id'] = str_replace('"', '', $value);
    }

    /**
     * [setCorrectorsAttribute description]
     * @param [type] $value [description]
     */
    public function setCorrectorsAttribute($value)
    {
        if(is_array($value)) {
            $this->attributes['correctors'] = json_encode($value);
        } else {
            $this->attributes['correctors'] = str_replace('"', '', $value);
        }
    }

    /**
     * [getCorrectorsNameAttribute description]
     * @return [type] [description]
     */
    public function getCorrectorsNameAttribute()
    {
        if($this->correctors != '') {
            $correctors = User::whereIn('id', $this->correctors)->select('id','name')->get();
            return $correctors;
        }
        return 0;
    }

    /**
     * [getAgamaAttribute description]
     * @return [type] [description]
     */
    public function getAgamaAttribute()
    {
        if($this->agama_id != '0') {
            $agama = DB::table('agamas')->where('id', $this->agama_id)->first();
            return $agama->nama;
        }
        return 0;
    }
}

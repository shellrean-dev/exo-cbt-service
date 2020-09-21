<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];

    /**
     * [$appends description]
     * @var [type]
     */
    protected $appends = [
        'kode_banksoal','ids', 'banksoal'
    ];

    /**
     * [$hidden description]
     * @var [type]
     */
    protected $hidden = [
        'created_at','updated_at','ids', 'banksoal'
    ];

    /**
     * [$casts description]
     * @var [type]
     */
    protected $casts = [
        'banksoal_id' => 'array',
        'ids' => 'array',
        'setting'   => 'array'
    ];

    /**
     * [banksoal description]
     * @return [type] [description]
     */
    public function banksoal() 
    {
        return $this->hasOne(Banksoal::class,'id','banksoal_id');
    }

    /**
     * [event description]
     * @return [type] [description]
     */
    public function event()
    {
        return $this->belongsTo(EventUjian::class);
    }   

    /**
     * [getKodeBanksoalAttribute description]
     * @return [type] [description]
     */
    public function getKodeBanksoalAttribute()
    {
        $ids = array_column($this->banksoal_id, 'id');
        return Banksoal::whereIn('id', $ids)->get()->pluck('kode_banksoal');
    }

    /**
     * [getIdsAttribute description]
     * @return [type] [description]
     */
    public function getIdsAttribute()
    {
        $this->casts['banksoal_id'] = 'string';
        return $this->banksoal_id;
    }

    /**
     * [getBanksoalAttribute description]
     * @return [type] [description]
     */
    public function getBanksoalAttribute()
    {
        $ids = array_column($this->ids, 'id');
        $banksoal = Banksoal::whereIn('id', $ids)->get();
        return $banksoal;
    }
}

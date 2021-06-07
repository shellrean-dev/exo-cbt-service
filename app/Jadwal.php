<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
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
    protected $appends = [
        'kode_banksoal','ids', 'banksoal', 'groups'
    ];

    /**
     * [$hidden description]
     * @var [type]
     */
    protected $hidden = [
        'created_at','updated_at','ids', 'banksoal','groups'
    ];

    /**
     * [$casts description]
     * @var [type]
     */
    protected $casts = [
        'banksoal_id' => 'array',
        'group_ids' => 'array',
        'ids' => 'array',
        'setting'   => 'array',
        'mulai_sesi'    => 'array'
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
     * Data Sesi Schedule
     * @return SesiSchedule
     */
    public function sesi()
    {
        return $this->hasMany(SesiSchedule::class);
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
     * Get group name
     * @return array
     */
    public function getGroupsAttribute()
    {
        if ($this->group_ids != null && $this->group_ids != '') {
            $ids = array_column($this->group_ids, 'id');
            return Group::whereIn('id', $ids)->get()->pluck('name'); 
        }
        return [];
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

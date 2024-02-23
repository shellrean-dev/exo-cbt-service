<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use Illuminate\Support\Carbon;

class Soal extends Model
{
    use Uuids;

    /**
     * [$guarded description]
     * @var array
     */
    protected $guarded = [];

    /**
     * [$hidden description]
     * @var [type]
     */
    protected $hidden = [
        'analys','diagram','salah','benar', 'penjawab', 'kosong'
    ];

    /**
     * [$appends description]
     * @var [type]
     */
    protected $appends = [
        'diagram','salah','benar', 'penjawab', 'kosong'
    ];

    /**
     * [$casts description]
     * @var [type]
     */
    protected $casts = [
        'analys'    => 'array'
    ];

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i');
    }

    /**
     * [banksoal description]
     * @return [type] [description]
     */
    public function banksoal()
    {
        return $this->belongsTo(Banksoal::class,'banksoal_id');
    }

    /**
     * [jawabans description]
     * @return [type] [description]
     */
    public function jawabans()
    {
        return $this->hasMany(JawabanSoal::class, 'soal_id','id')->orderBy('created_at');
    }

    /**
     * [getDiagramAttribute description]
     * @return [type] [description]
     */
    public function getDiagramAttribute()
    {
        $array = array();
        $array[0] = ['Task','Value'];
        if(!is_array($this->analys)) {
            return $array;
        }
        foreach($this->analys as $key => $value)
        {
            if($key == 'updated' || $key == 'penjawab') {
                continue;
            }
            $array[] = [
                $key, $value
            ];
        }
        return $array;
    }

    /**
     * [getSalahAttribute description]
     * @return [type] [description]
     */
    public function getSalahAttribute()
    {
        if($this->tipe_soal == 2) {
            return 0;
        }

        $salah = JawabanPeserta::where([
            'soal_id' => $this->id,
            'iscorrect' => 0
        ])->count();

        return $salah;
    }

    /**
     * [getBenarAttribute description]
     * @return [type] [description]
     */
    public function getBenarAttribute()
    {
        $benar = JawabanPeserta::where([
            'soal_id'   => $this->id,
            'iscorrect' => 1
        ])->count();

        return $benar;
    }

    /**
     * [getPenjawabAttribute description]
     * @return [type] [description]
     */
    public function getPenjawabAttribute()
    {
        $penjawab = JawabanPeserta::where([
            'soal_id'   => $this->id
        ])->count();

        return $penjawab;
    }

    /**
     * [getKosongAttribute description]
     * @return [type] [description]
     */
    public function getKosongAttribute()
    {
        if($this->tipe_soal == 2) {
            return 0;
        }

        $kosong = JawabanPeserta::where([
            'soal_id'       => $this->id,
            'jawab'         => 0
        ])->count();

        return $kosong;
    }
}

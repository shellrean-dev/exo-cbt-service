<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class JawabanPeserta extends Model
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
        'created_at','udpated_at'
    ];

    /**
     * [$appends description]
     * @var [type]
     */
    protected $appends = [
        'similiar'
    ];

    protected $casts = [
        'jawab_complex' => 'array',
    ];

    /**
     * [pertanyaan description]
     * @return [type] [description]
     */
    public function pertanyaan()
    {
        return $this->belongsTo(Soal::class,'soal_id','id');
    }

    /**
     * [getSimiliarAttribute description]
     * @return [type] [description]
     */
    public function getSimiliarAttribute()
    {
        $text = Soal::find($this->soal_id);
        if($this->esay != null && $text->tipe_soal == 2) {
            $rujukan = strip_tags($text->rujukan);

            $jawab = strip_tags($this->esay);
            similar_text($rujukan, $jawab, $percent);

            return $percent;
        }
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    /**
     * [soal description]
     * @return [type] [description]
     */
    public function soal()
    {
        return $this->belongsTo('App\Soal','soal_id');
    }

    public function group()
    {
        return $this->hasOne(GroupMember::class, 'student_id', 'peserta_id');
    }

    /**
     * [banksoal description]
     * @return [type] [description]
     */
    public function banksoal()
    {
        return $this->belongsTo(Banksoal::class);
    }

    /**
     * [esay_result description]
     * @return [type] [description]
     */
    public function esay_result()
    {
        return $this->hasOne(PenilaianEsay::class,'jawab_id');
    }
}

<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Banksoal extends Model
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
        'inputed','koreksi'
    ];

    protected $casts = [
        'persen'        => 'array'
    ];

    /**
     * [$hidden description]
     * @var [type]
     */
    protected $hidden = [
        'created_at','updated_at','author','koreksi'
    ];

    /**
     * [pertanyaans description]
     * @return [type] [description]
     */
    public function pertanyaans()
    {
        return $this->hasMany(Soal::class, 'banksoal_id','id');
    }

    /**
     * [matpel description]
     * @return [type] [description]
     */
    public function matpel()
    {
        return $this->belongsTo(Matpel::class,'matpel_id');
    }

    /**
     * [user description]
     * @return [type] [description]
     */
    public function user()
    {
        return $this->belongsTo(User::class,'author');
    }

    /**
     * [ujian description]
     * @return [type] [description]
     */
    public function ujian()
    {
        return $this->hasMany(Jadwal::class);
    }

    /**
     * [getInputedAttribute description]
     * @return [type] [description]
     */
    public function getInputedAttribute()
    {
        $count = Soal::where('banksoal_id', $this->id)->count();
        return $count;
    }

    /**
     * [getKoreksiAttribute description]
     * @return [type] [description]
     */
    public function getKoreksiAttribute()
    {
        $exists = PenilaianEsay::where('banksoal_id', $this->id)
        ->get()
        ->pluck('jawab_id')
        ->unique();

        return JawabanPeserta::where(function($query) use($exists){
            $query->whereNotIn('id', $exists)
            ->whereHas('soal', function($query) {
                $query->where('tipe_soal','=', '2');
            })
            ->whereNotNull('esay')
            ->where('banksoal_id', $this->id);
        })
        ->count();
    }
}

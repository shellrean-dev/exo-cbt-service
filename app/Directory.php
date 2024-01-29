<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Directory extends Model
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
        'created_at','updated_at'
    ];

    /**
     * [$appends description]
     * @var [type]
     */
    protected $appends = [
        'size'
    ];

    /**
     * [file description]
     * @return [type] [description]
     */
    public function file()
    {
        return $this->hasMany(File::class);
    }

    /**
     * [getSizeAttribute description]
     * @return [type] [description]
     */
    public function getSizeAttribute()
    {
        return File::where('directory_id', $this->id)->get()->sum('size');
    }

    public function banksoal()
    {
        return $this->hasOne(Banksoal::class);
    }
}

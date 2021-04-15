<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class EventUjian extends Model
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

    public function ujians()
    {
        return $this->hasMany(Jadwal::class, 'event_id');
    }
}

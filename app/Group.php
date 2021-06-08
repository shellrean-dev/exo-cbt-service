<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use Uuids;

    /**
     * get group's children
     * @return object
     * @author shellrean <wandinak17@gmail.com>
     */
    public function childs()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

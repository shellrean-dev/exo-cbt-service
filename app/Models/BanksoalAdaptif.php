<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Banksoal adaptif control data adaptif
 * mechanism for core controller data
 * 
 * @author shellrean <wandinak17@gmail.com>
 */
class BanksoalAdaptif extends Model
{
    use Uuids;

    /**
     * declare table name
     * @var string
     */
    protected $table = 'banksoal_adaptif';

    /**
     * set fillable that can 
     * access and insert to it
     * @var array
     */
    protected $fillable = [
        'matpel_id', 'name', 'code', 'max_pg'
    ];

    /**
     * get matpel relationship
     * @return BelongsTo
     */
    public function matpel()
    {
        return $this->belongsTo(\App\Matpel::class, 'matpel_id');
    }
}
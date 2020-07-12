<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SoalCollection;
use App\Soal;

class SoalController extends Controller
{
    /**
     * Get the soal all from banksoal
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showByBanksoal($id)
    {
        $soal = Soal::where(['banksoal_id' => $id]);

        if (request()->q != '') {
            $soal = $soal->where('pertanyaan','LIKE', '%'.request()->q.'%');
        }   

        $soal = $soal->paginate(10);
        return new SoalCollection($soal);
    }
}

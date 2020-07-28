<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\Directory;
use App\Banksoal;
use App\Soal;

class BanksoalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author shellrean <wandinak17@gmail.com>
     * @return /App/Actions/SendResponse
     */
    public function index()
    {
        $user = request()->user('api');
        $perPage = request()->perPage ?: '';

        $banksoal = Banksoal::with(['matpel','user'])->orderBy('id', 'DESC');
        if (request()->q != '') {
            $banksoal = $banksoal->where('kode_banksoal', 'LIKE', '%'. request()->q.'%');
        }
        if ($user->role != 'admin') {
            $banksoal = $banksoal->where('author',$user->id);
        }
        if($perPage != '') {
            $banksoal = $banksoal->paginate($perPage);
        } else {
            $banksoal = $banksoal->get();
        }
        return SendResponse::acceptData($banksoal);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @author shellrean <wandinak17@gmail.com>
     * @return /App/Actions/SendResponse
     */
    public function store(Request $request)
    {
        $user = request()->user('api');
        $request->validate([
            'kode_banksoal'     => 'required|unique:banksoals,kode_banksoal',
            'matpel_id'         => 'required|exists:matpels,id',
            'jumlah_soal'       => 'required|int',
            'jumlah_pilihan'    => 'required|int',
            'jumlah_soal_listening' => 'required|int',
            'jumlah_pilihan_listening' => 'required|int'
        ]);

        DB::beginTransaction();

        try {
            $direk = Directory::create([
                'name'      => $request->kode_banksoal,
                'slug'      => Str::slug($request->kode_banksoal, '-')
            ]);

            $data = [
                'kode_banksoal'     => $request->kode_banksoal,
                'matpel_id'         => $request->matpel_id,
                'author'            => $user->id,
                'jumlah_soal'       => $request->jumlah_soal,
                'jumlah_pilihan'    => $request->jumlah_pilihan,
                'jumlah_soal_esay'  => $request->jumlah_soal_esay,
                'jumlah_soal_listening' => $request->jumlah_soal_listening,
                'jumlah_pilihan_listening' => $request->jumlah_pilihan_listening,
                'directory_id'      => $direk->id
            ];

            $res = Banksoal::create($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::acceptData($res);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @author shellrean <wandinak17@gmail.com>
     * @return /App/Actions/SendResponse
     */
    public function show(Banksoal $banksoal)
    {
        $banksoal = Banksoal::with('matpel')->find($banksoal->id);
        return SendResponse::acceptData($banksoal);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @author shellrean <wandinak17@gmail.com>
     * @return /App/Actions/SendResponse
     */
    public function update(Request $request, Banksoal $banksoal)
    {
        $request->validate([
            'kode_banksoal'     => 'required|unique:banksoals,kode_banksoal,'.$banksoal->id,
            'jumlah_soal'       => 'required|int',
            'jumlah_pilihan'    => 'required|int',
            'jumlah_soal_listening' => 'required|int',
            'jumlah_pilihan_listening' => 'required|int'
        ]);

        $banksoal->kode_banksoal = $request->kode_banksoal;
        if(gettype($request->matpel_id) == 'array') {
            $banksoal->matpel_id = $request->matpel_id['id'];
        }

        $banksoal->jumlah_soal = $request->jumlah_soal;
        $banksoal->jumlah_pilihan = $request->jumlah_pilihan;
        $banksoal->jumlah_soal_esay = $request->jumlah_soal_esay;
        $banksoal->jumlah_soal_listening = $request->jumlah_soal_listening;
        $banksoal->jumlah_pilihan_listening = $request->jumlah_pilihan_listening;
        $banksoal->save();

        return SendResponse::acceptData($banksoal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @author shellrean <wandinak17@gmail.com>
     * @return /App/Actions/SendResponse
     */
    public function destroy(Banksoal $banksoal)
    {
        DB::beginTransaction();

        try {
            $banksoal->delete();
            Directory::find($banksoal->directory_id)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage()); 
        }
        return SendResponse::accept();
    }

    /**
     * Display all data.
     *
     * @author shellrean <wandinak17@gmail.com>
     * @return /App/Actions/SendResponse
     */
    public function allData()
    {
        $user = request()->user('api');
        $banksoal = Banksoal::with(['matpel'])->orderBy('id', 'DESC');
        if ($user->role != 'admin') {
            $banksoal = $banksoal->where('author',$user->id);
        }
        $banksoal = $banksoal->get();
        return SendResponse::acceptData($banksoal);
    }

    /**
     * [getAnalys description]
     * @param  Banksoal $banksoal [description]
     * @return [type]             [description]
     */
    public function getAnalys(Banksoal $banksoal)
    {
        $soal = Soal::with('jawabans')->where(function($query) use ($banksoal) {
            $query->where('banksoal_id', $banksoal->id)
            ->where('tipe_soal','!=','2');
        })->get();

        $fill = $soal->map(function($val, $key) {
            $jawab = JawabanPeserta::where('soal_id', $val->id)->get();
            $penjawab = $jawab->count();
            $salah = $jawab->where('iscorrect', '0')->count();
            $benar = $jawab->where('iscorrect','1')->count();
            return [
                'soal'  => $val->pertanyaan,
                'penjawab' => $penjawab,
                'salah'     => $salah,
                'benar' => $benar,
                'diagram' => [
                    ['Tas','value'],
                    ['salah', $salah],
                    ['benar',$benar]
                ],
                'jawaban' => $val->jawabans->map(function($vel, $kiy) use($jawab) {
                    return [
                        'text' => $vel->text_jawaban,
                        'iscorrect' => $vel->correct,
                        'penjawab' => $jawab->where('jawab',$vel->id)->count()
                    ];
                }),
            ];
        });

        return SendResponse::acceptData($fill);
    }
}

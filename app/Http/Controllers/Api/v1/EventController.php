<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\EventUjian;
use App\Jadwal;
use App\Models\UjianConstant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * EventController
 * @author shellrean <wandinak17@gmail.com>
 */
class EventController extends Controller
{
    /**
     * @Route(path="api/v1/events", methods={"GET"})
     *
     * Display a listing of the resource.
     *
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = isset(request()->paerPage) ? request()->perPage : 10;
        $search = isset(request()->q) ? request()->q : '';

        $events = EventUjian::with(['ujians' => function($query) {
            $query->select('id','alias','tanggal','mulai','event_id','banksoal_id')->orderBy('tanggal')->orderBy('mulai');
        }])->orderBy('id');
        if($search != '') {
            $events = $events->where('name','LIKE','%'.$search.'%');
        }
        $events = $events->paginate($perPage);
        return SendResponse::acceptData($events);
    }

    /**
     * @Route(path="api/v1/events", methods={"POST"})
     *
     * Store a newly created resource in storage.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        EventUjian::create([
            'name' => $request->name
        ]);

        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/events/{id}", methods={"GET"})
     *
     * Display the specified resource.
     *
     * @param  App\EventUjian $event
     * @return Illuminate\Http\Response
     */
    public function show(EventUjian $event)
    {
        return SendResponse::acceptData($event);
    }

    /**
     * @Route(path="api/v1/events/{id}", methods={"PUT", "PATCH"})
     *
     * Update the specified resource in storage.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  App\EventUjian $event
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, EventUjian $event)
    {
        $request->validate([
            'name'      => 'required'
        ]);

        $event->update([
            'name'      => $request->name
        ]);
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/events/{id}", methods={"DELETE"})
     *
     * Remove the specified resource from storage.
     *
     * @param  App\EventUjian $event
     * @return Illuminate\Http\Response
     */
    public function destroy(EventUjian $event)
    {
        $event->delete();
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/events/all", methods={"GET"})
     *
     * @return App\Actions\SendResponse
     */
    public function allData()
    {
        $events = EventUjian::orderBy('created_at','DESC')->get();
        return SendResponse::acceptData($events);
    }

    /**
     * @Route(path="api/v1/events/{event_id}/ujian", methods={"GET"})
     *
     * Get event detail
     *
     * @param string $event_id
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function eventDetailData($event_id)
    {
        $event = EventUjian::find($event_id);
        $jadwal = Jadwal::with('sesi')->where('event_id', $event_id)
        ->orderBy('tanggal')->orderBy('mulai')
        ->select('id', 'alias','tanggal','mulai', 'mulai_sesi')
        ->get()
        ->makeHidden('kode_banksoal');

        return SendResponse::acceptData([
            'event' => $event,
            'jadwal' => $jadwal->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->alias,
                    'tanggal' => $item->tanggal,
                    'mulai' => $item->mulai,
                    'mulai_sesi' => $item->mulai_sesi,
                    'sesi' => $item->sesi->map(function($sesi) {
                        return [
                            'sesi' => $sesi->sesi,
                            'peserta' => $sesi->peserta_ids
                        ];
                    })
                ];
            }),
        ]);
    }

    /**
     * @Route(path="api/v1/events/ujian/{jadwal_id}/summary-simple", methods={"GET"})
     *
     * Get event summary
     *
     * @param string $jadwal_id
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function summarize($jadwal_id)
    {
        $ujian = DB::table('jadwals as t_0')
            ->join('event_ujians as t_1', 't_0.event_id', 't_1.id')
            ->where('t_0.id', $jadwal_id)
            ->select([
                't_0.id',
                't_0.alias as jadwal_name',
                't_1.name as event_name'
            ])
            ->first();

        if (!$ujian) {
            return SendResponse::badRequest('jadwal ujian tidak valid');
        }

        $peserta_finish = DB::table('siswa_ujians as t_0')
            ->where('t_0.jadwal_id', $ujian->id)
            ->where('t_0.status_ujian', UjianConstant::STATUS_FINISHED)
            ->count();

        $peserta_onprogress = DB::table('siswa_ujians as t_0')
            ->where('t_0.jadwal_id', $ujian->id)
            ->whereIn('t_0.status_ujian', [UjianConstant::STATUS_STANDBY, UjianConstant::STATUS_PROGRESS])
            ->count();

        $sesi_schedule = DB::table('sesi_schedules as t_0')->where('t_0.jadwal_id', $ujian->id)->get();

        $peserta_ids = [];
        foreach($sesi_schedule as $sesi) {
            $pesertas = json_decode($sesi->peserta_ids, true);
            $peserta_ids = array_merge($peserta_ids, $pesertas);
        }

        $all_peserta_ujian = DB::table('siswa_ujians as t_0')
            ->where('t_0.jadwal_id', $ujian->id)
            ->whereIn('t_0.peserta_id', $peserta_ids)
            ->count();

        $not_start = count($peserta_ids) - $all_peserta_ujian;

        $data = [
            'id'    => $ujian->id,
            'event_name' => $ujian->event_name,
            'jadwal_name' => $ujian->jadwal_name,
            'finish'     => $peserta_finish,
            'on_work' => $peserta_onprogress,
            'no_start' => $not_start,
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        return SendResponse::acceptData($data);
    }

    /**
     * @Route(path="api/v1/events/ujian/{jadwal_id}/peserta-not-start", methods={"GET"})
     *
     * Get jadwal peserta not work
     *
     * @param string $jadwal_id
     * @author shellrean <wandinak17@gmail.com>
     */
    public function pesertaNotWork($jadwal_id)
    {
        $ujian = DB::table('jadwals as t_0')
            ->join('event_ujians as t_1', 't_0.event_id', 't_1.id')
            ->where('t_0.id', $jadwal_id)
            ->select([
                't_0.id',
                't_0.alias as jadwal_name',
                't_1.name as event_name'
            ])
            ->first();

        if (!$ujian) {
            return SendResponse::badRequest('jadwal ujian tidak valid');
        }

        $sesi_schedule = DB::table('sesi_schedules as t_0')->where('t_0.jadwal_id', $ujian->id)->get();

        $peserta_ids = [];
        foreach($sesi_schedule as $sesi) {
            $pesertas = json_decode($sesi->peserta_ids, true);
            $peserta_ids = array_merge($peserta_ids, $pesertas);
        }

        $all_peserta_ujian = DB::table('siswa_ujians as t_0')
            ->where('t_0.jadwal_id', $ujian->id)
            ->whereIn('t_0.peserta_id', $peserta_ids)
            ->select(['t_0.peserta_id'])
            ->get()
            ->pluck('peserta_id')
            ->toArray();

        $id_diff = array_values(array_diff($peserta_ids, $all_peserta_ujian));

        $peserta = DB::table('pesertas as t_0')
            ->whereIn('t_0.id', $id_diff)
            ->select([
                't_0.id',
                't_0.no_ujian',
                't_0.nama'
            ])
            ->orderBy('t_0.created_at')
            ->limit(100)
            ->get();

        return SendResponse::acceptData($peserta);
    }
    
    /**
     * @Route(path="api/v1/events/ujian/{jawal_id}/peserta-in-sesi", methods={"GET"})
     *
     * Get jadwal peserta not work
     *
     * @param string $jadwal_id
     * @author shellrean <wandinak17@gmail.com>
     */
    public function peserta_in_sesi($jadwal_id)
    {
        $sesi = request()->q;
        if (!in_array(intval($sesi), [1,2,3,4])) {
            return SendResponse::badRequest('sesi invalid');
        }

        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->select('id','alias','event_id')
            ->first();
        if (!$jadwal) {
            return SendResponse::badRequest('jadwal tidak ditemukan');
        }

        $check = DB::table('sesi_schedules')
            ->where('jadwal_id', $jadwal_id)
            ->where('sesi', $sesi)
            ->count();
        if ($check < 1) {
            return SendResponse::badRequest('tidak ada peserta pada sesi tersebut');
        }

        $event = DB::table('event_ujians')
            ->where('id', $jadwal->event_id)
            ->first();
        if (!$event) {
            return SendResponse::badRequest('event ujian tidak ditemukan');
        }

        $sesi = DB::table('sesi_schedules')
            ->where('jadwal_id', $jadwal_id)
            ->where('sesi', $sesi)
            ->select('id','peserta_ids')
            ->first();
        if (!$sesi) {
            return SendResponse::badRequest('sesi tidak ditemukan');
        }

        $students = DB::table('pesertas')
            ->whereIn('id', json_decode($sesi->peserta_ids, true))
            ->select('id','no_ujian','nama')
            ->get();

        return SendResponse::acceptData($students);
    }
}

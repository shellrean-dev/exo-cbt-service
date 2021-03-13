<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Exports\KartuPesertaPdf;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\EventUjian;
use App\Jadwal;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(EventUjian $event)
    {
        return SendResponse::acceptData($event);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(EventUjian $event)
    {
        $event->delete();
        return SendResponse::accept();
    }

    /**
     * [allData description]
     * @return [type] [description]
     */
    public function allData()
    {
        $events = EventUjian::orderBy('id','DESC')->get();
        return SendResponse::acceptData($events);
    }

    /**
     * Get event detail
     * 
     * @author shellrean <wandinak17@gmail.com>
     * @param \App\Repositories\EventRepository
     * @param @event_id
     * @return \App\Actions\SendResponse
     */
    public function eventDetailData($event_id)
    {
        $event = EventUjian::find($event_id);
        $jadwal = Jadwal::with('sesi')->where('event_id', $event_id)
        ->orderBy('tanggal')->orderBy('mulai')
        ->select('id', 'alias','tanggal','mulai')
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
     * Buat kartu peserta
     */
    public function kartu_peserta()
    {
        $obj = new KartuPesertaPdf();
        $obj->generate();
        $obj->show();
    }
}

<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\EventUjian;
use App\Jadwal;

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
}

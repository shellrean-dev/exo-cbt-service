<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Directory;
use App\File;

/**
 * DirectoryController
 * @author shellrean <wandinak17@gmail.com>
 */
class DirectoryController extends Controller
{
    /**
     * @Route(path="api/v1/directory", methods={"GET"})
     * 
     * Display a listing of the resource.
     *
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $directories = Directory::withCount(['file'])->paginate(20);
        return SendResponse::acceptData($directories);
    }

    /**
     * @Route(path="api/v1/directory", methods={"POST"})
     * 
     * Store a newly created resource in storage.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_directory'    => 'required|unique:directories,name',
        ]);

        Directory::create([
            'name'      => $request->nama_directory,
            'slug'      => Str::slug($request->nama_directory, '-')
        ]);
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/directory/{id}", methods={"GET"})
     * 
     * Display the specified resource.
     *
     * @param  App\Directory $directory
     * @return Illuminate\Http\Response
     */
    public function show(Directory $directory)
    {
        $contentDirectory = File::where(['directory_id' => $directory->id]);
        $contentDirectory = $contentDirectory->paginate(50);
        return SendResponse::acceptData($contentDirectory);
    }
    
    /**
     * @Route(path="api/v1/upload/file-audio", methods={"POST"})
     * 
     * Update the specified resource in storage.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Http\Response
     */
    public function uploadAudio(Request $request)
    {
        $file = $request->file('file');
        $filename = date('Ymd').'-'.$file->getClientOriginalName();
        $path = $file->storeAs('public/audio/',$filename);

        return response()->json(['data' => $filename]);
    }

    /**
     * @Route(path="api/v1/directory/filemedia", methods={"POST"})
     * 
     * Insert filemedia.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Http\Response
     */
    public function storeFilemedia(Request $request)
    {
        $dir = Directory::find($request->directory_id);
        $file = $request->file('image');
        $type = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $filename = date('Ymd').'-'.$file->getClientOriginalName();
        $path = $file->storeAs('public/'.$dir->slug,$filename);

        $data= [
            'directory_id'      => $request->directory_id,
            'filename'          => $filename,
            'path'              => $path,
            'exstension'        => $type,
            'dirname'           => $dir->slug,
            'size'              => $size,
        ];

        $logo = File::create($data);

        return response()->json(['data' => $logo]);
    }

    /**
     * @Route(path="api/v1/file/upload", methods={"POST"})
     * 
     * Upload file
     * 
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function uploadFile(Request $request)
    {
        if ($request->hasFile('upload')) {
            $dir = Directory::find(request()->directory_id);
            if(!$dir) {
                return response()->json(['error' => true, 'message' => 'error, directory notfound'], 400);
            }
            $file = $request->file('upload');
            $type = $file->getClientOriginalExtension();
            $size = $file->getSize();
            $filename = date('Ymd').'-'.$file->getClientOriginalName();
            $path = $file->storeAs('public/'.$dir->slug,$filename);

            $data= [
                'directory_id'      => request()->directory_id,
                'filename'          => $filename,
                'path'              => $path,
                'exstension'        => $type,
                'dirname'           => $dir->slug,
                'size'              => $size,
            ];

            $image = File::create($data);
            $url = asset('storage/'.$dir->slug.'/' . $filename); 
            return response()->json([
                'uploaded' => 1,
                'fileName' => $filename,
                'url' => $url
            ]);
        }
    }
    
    /**
     * @Route(path="api/v1/directory/filemedia/{id}", methods={"DELETE"})
     * 
     * @param  string $id
     * @return Illuminate\Http\Response
     */
    public function deleteFilemedia($id)
    {
        $file = File::find($id);
        if(file_exists(storage_path('app/'.$file->path))) {
            unlink(storage_path('app/'.$file->path));
        }
        $file->delete();
        return response()->json([],200);
    }

    /**
     * @Route(path="api/v1/directory/filemedia/multiple-delete", methods={"GET"})
     * 
     * Delete filemedia multiple
     * 
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function deleteMultipleFilemedia()
    {
        try {
            $q = request()->q;
            $ids = explode(',', $q);

            $files = DB::table('files')
                ->whereIn('id', $ids)
                ->select('id','path')
                ->get();

            foreach($files as $file) {
                if(file_exists(storage_path('app/'.$file->path))) {
                    unlink(storage_path('app/'.$file->path));
                }
            }
            DB::table('files')
                ->whereIn('id', $ids)
                ->delete();
        } catch (\Exception $e) {
            return SendResponse::internalServerError(sprintf("kesalahan 500 (%s)", $e->getMessage()));
        }
    }

    /**
     * @Route(path="api/v1/directory/banksoal/{id}", methods={"GET"})
     * 
     * Get directory banksoal
     * 
     * @param  string $id
     * @return Illuminate\Http\Response
     */
    public function getDirectoryBanksoal($id)
    {
        $contentDirectory = File::where(['directory_id' => $id]);
        $contentDirectory = $contentDirectory->paginate(10);
        return [ 'data' => $contentDirectory ];
    }
}

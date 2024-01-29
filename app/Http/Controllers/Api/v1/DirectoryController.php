<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Directory;
use App\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

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
    public function index(Request $request)
    {
        $user = $request->user();
        $directories = Directory::withCount(['file']);
        if ($user->role == 'guru') {
            $directories = $directories->whereHas('banksoal', function ($query) use ($user) {
                return $query->where('author', '=', $user->id);
            });
        }
        $directories = $directories->paginate(20);
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
        $contentDirectory = $contentDirectory
            ->paginate(50);
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
        $file->storeAs('audio/',$filename);

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

        $filename = sprintf('%s.%s', Str::uuid()->toString(), $type);

        if (in_array($type, ['png', 'jpg', 'jpeg'])) {
            $path = sprintf('%s/%s.webp', $dir->slug, $filename);
            $filename = $filename.'.webp';

            $image = Image::make($file)->encode('webp', 90);
            Storage::put($path, $image->__toString());
        } else {
            $path = $file->storeAs($dir->slug, $filename);
        }

        $data= [
            'directory_id'      => $request->directory_id,
            'filename'          => $filename,
            'path'              => sprintf('/storage/%s', $path),
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

            $filename = sprintf('%s.%s', Str::uuid()->toString(), $type);

            if (in_array($type, ['png', 'jpg', 'jpeg'])) {
                $path = sprintf('%s/%s.webp', $dir->slug, $filename);
                $filename = $filename.'.webp';

                $image = Image::make($file)->encode('webp', 90);
                Storage::put($path, $image->__toString());
            } else {
                $path = $file->storeAs($dir->slug, $filename);
            }

            $data= [
                'directory_id'      => request()->directory_id,
                'filename'          => $filename,
                'path'              => sprintf('/storage/%s', $path),
                'exstension'        => $type,
                'dirname'           => $dir->slug,
                'size'              => $size,
            ];

            $image = File::create($data);
            return response()->json([
                'uploaded' => 1,
                'fileName' => $filename,
                'url' => $data['path'],
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
        if(file_exists(public_path($file->path))) {
            unlink(public_path($file->path));
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
                if(file_exists(public_path($file->path))) {
                    unlink(public_path($file->path));
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

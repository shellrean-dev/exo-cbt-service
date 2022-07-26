<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * BackupController Controller
 * @author shellrean <wandinak17@gmail.com>
 */
class BackupController extends Controller
{
    /**
     * @Route(path="api/v1/system/backup", methods={"GET"})
     * 
     * List audit
     *
     * @param BackupService $backupService
     * @return \Illuminate\Http\Response
     * @since 3.16.0
     */
    public function index()
    {
        $data = DB::table("exo_backups")->orderByDesc("id")->get();
        return SendResponse::acceptCustom([
            'data' => $data,
            'secret_key' => config('app.key')
        ]);
    }

    /**
     * @Route(path="api/v1/system/backup", methods={"GET"})
     *
     * Backup system
     *
     * @param BackupService $backupService
     * @return \Illuminate\Http\Response
     * @since 3.16.0
     */
    public function backup(BackupService $backupService)
    {
        $backupService->backup();
        return SendResponse::accept("backup berhasil dibuat");
    }

    /**
     * @Route(path="api/v1/system/restore", methods={"POST"})
     *
     * Restore system
     *
     * @param \Illuminate\Http\Request $request
     * @param BackupService $backupService
     * @return \Illuminate\Http\Response
     * @since 3.16.0
     */
    public function restore(Request $request, BackupService $backupService)
    {
        try {
            $file = $request->file('file');
            if($file->getError() != UPLOAD_ERR_OK) {
                return SendResponse::internalServerError($file->getErrorMessage());
            }

            $fileString = $file->get();
            $data = $backupService->restore($fileString, $file->getClientOriginalName());
            return SendResponse::accept('restore success');
        } catch (DecryptException $e) {
            return SendResponse::badRequest('Enkripsi failed, cek key dan file ('.$e->getMessage().')');
        } catch (Exception $e) {
            return SendResponse::internalServerError($e->getMessage());
        }
    }

    /**
     * @Route(path="api/v1/system/backup-download/{id}/proxy", methods={"GET"})
     *
     * Restore system
     *
     * @param \Illuminate\Http\Request $request
     * @param BackupService $backupService
     * @return \Illuminate\Http\Response
     * @since 3.16.0
     */
    public function proxyLinkDownload($backupId)
    {
        $backup = DB::table('exo_backups')->where('id', $backupId)->first();
        if(!$backup) {
            return SendResponse::badRequest('backup tidak ditemukan');
        }

        if(!Storage::exists('public'.DIRECTORY_SEPARATOR.'backup'.DIRECTORY_SEPARATOR.$backup->filename)) {
            return SendResponse::badRequest('backup tidak ditemukan pada direktori');
        }

        $url = URL::temporarySignedRoute(
            'backup.download',
            now()->addMinutes(5),
            ['backup_id' => $backup->id]
        );

        return SendResponse::acceptData($url);
    }

    /**
     * @Route(path="api/v1/system/backup-download/{id}/download", methods={"GET"})
     *
     * Restore system
     *
     * @param \Illuminate\Http\Request $request
     * @param BackupService $backupService
     * @return \Illuminate\Http\Response
     * @since 3.16.0
     */
    public function download($backupId)
    {
        if (! request()->hasValidSignature()) {
            return SendResponse::badRequest('Kesalahan, url tidak valid');
        }

        $backup = DB::table('exo_backups')->where('id', $backupId)->first();
        if(!$backup) {
            return SendResponse::badRequest('backup tidak ditemukan');
        }

        return Storage::download('public'.DIRECTORY_SEPARATOR.'backup'.DIRECTORY_SEPARATOR.$backup->filename);
    }
}

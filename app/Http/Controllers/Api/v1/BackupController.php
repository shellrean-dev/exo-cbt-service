<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * BackupController Controller
 * @author shellrean <wandinak17@gmail.com>
 */
class BackupController extends Controller
{
    /**
     * @Route(path="api/v1/system/backup", methods={"GET"})
     * 
     * Backup system
     */
    public function backup(BackupService $backupService)
    {
        $backupService->backup();
        return SendResponse::accept("backup-created");
    }

    /**
     * @Route(path="api/v1/system/restore", methods={"POST"})
     * 
     * Restore system
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
            return SendResponse::badRequest($e->getMessage());
        } catch (Exception $e) {
            return SendResponse::internalServerError($e->getMessage());
        }
    }
}

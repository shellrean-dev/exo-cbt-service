<?php
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Backup system
 * @author shellrean <wandinak17@gmail.com>
 */
class BackupService
{
    public const JURUSAN_SECTIONS = "jurusan";
    public const MATPEL_SECTIONS = "matpel";
    public const DIRECTORIES_SECTIONS = "directories";
    public const BANKSOAL_SECTION = "banksoals";
    public const FILES_SECTION = "files";
    public const SOALS_SECTION = "soals";
    public const JAWABAN_SOAL_SECTION = "jawaban_soal";
    public const VERSION_SECTION = "version";

    /**
     * Backup exo-cbt data
     * This method will backup jurusan, matpel, directories, banksoals, soals and jawaban soals
     * You have to create app-key for your application
     */
    public function backup()
    {
        /**
         * Data preparation
         */
        $jurusans = DB::table("jurusans")->get();
        $matpels = DB::table("matpels")->get();
        $directories = DB::table("directories")->get();
        $banksoal = DB::table("banksoals")->get();
        $files = $this->mapFileBase64(DB::table("files")->get());
        $soals = DB::table("soals")->get();
        $jawaban_soals = DB::table("jawaban_soals")->get();

        /**
         * Data construction
         */
        $textInBackup = json_encode([
            BackupService::JURUSAN_SECTIONS => $jurusans,
            BackupService::MATPEL_SECTIONS => $matpels,
            BackupService::DIRECTORIES_SECTIONS => $directories,
            BackupService::BANKSOAL_SECTION => $banksoal,
            BackupService::FILES_SECTION => $files,
            BackupService::SOALS_SECTION => $soals,
            BackupService::JAWABAN_SOAL_SECTION => $jawaban_soals,
            BackupService::VERSION_SECTION => $this->retreiveVersionSection()
        ]);

        /**
         * Data encryption
         */
        $textInBackup = Crypt::encryptString($textInBackup);

        /**
         * File generation
         */
        $path = "public".DIRECTORY_SEPARATOR."backup".DIRECTORY_SEPARATOR.now()."-exo-backup.exo";
        Storage::put($path, $textInBackup);
    }

    /**
     * Restore exo-cbt data
     */
    public function restore($backupLiteral)
    {
        try {
            DB::beginTransaction();
            $textInBackup = Crypt::decryptString($backupLiteral);
            $dataInBackup = json_decode($textInBackup, true);

            if(isset($dataInBackup[BackupService::JURUSAN_SECTIONS])) {
                $this->restoreJurusanSection($dataInBackup[BackupService::JURUSAN_SECTIONS]);
            }
            if(isset($dataInBackup[BackupService::MATPEL_SECTIONS])) {
                $this->restoreMatpelSection($dataInBackup[BackupService::MATPEL_SECTIONS]);
            }
            if(isset($dataInBackup[BackupService::DIRECTORIES_SECTIONS])) {
                $this->restoreDirectoriesSection($dataInBackup[BackupService::DIRECTORIES_SECTIONS]);
            }
            if(isset($dataInBackup[BackupService::BANKSOAL_SECTION])) {
                $this->restoreBanksoalsSection($dataInBackup[BackupService::BANKSOAL_SECTION]);
            }
            if(isset($dataInBackup[BackupService::FILES_SECTION])) {
                $this->restoreFilesSection($dataInBackup[BackupService::FILES_SECTION]);
            }
            if(isset($dataInBackup[BackupService::SOALS_SECTION])) {
                $this->restoreSoalSection($dataInBackup[BackupService::SOALS_SECTION]);
            }
            if(isset($dataInBackup[BackupService::JAWABAN_SOAL_SECTION])) {
                $this->restoreJawabanSoalSection($dataInBackup[BackupService::JAWABAN_SOAL_SECTION]);
            }
            DB::commit();
            return $dataInBackup;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Map file base64
     * This file will get base64 file for files data
     * And also do filter if the file not found
     */
    private function mapFileBase64($files)
    {
        $files = $files->map(function($item) {
            $item->base64 = null;
            if(Storage::exists($item->path)) {
                $item->base64 = base64_encode(Storage::get($item->path));
            }
            
            return $item;
        })->filter(function($item) {
            return $item->base64 != null;
        });
        return $files;
    }

    private function retreiveVersionSection()
    {
        return [
            "code" => config("exo.version.code"),
            "time_generated" => now()
        ];
    }

    /**
     * Restore jurusan section
     * @param $jurusan array
     * @return void
     */
    private function restoreJurusanSection($jurusans)
    {
        DB::table("jurusans")->delete();
        DB::table("jurusans")->insert($jurusans);
    }

    /**
     * Restore matpel section
     * @param $matpels array
     * @return void
     */
    private function restoreMatpelSection($matpels)
    {
        DB::table("matpels")->delete();
        DB::table("matpels")->insert($matpels);
    }

    /**
     * Restore directories section
     * @param $directories array
     * @return void
     */
    private function restoreDirectoriesSection($directories)
    {
        DB::table("directories")->delete();
        DB::table("directories")->insert($directories);
    }

    /**
     * Restore banksoals section
     * @param $banksoals array
     * @return void
     */
    private function restoreBanksoalsSection($banksoals)
    {
        DB::table("banksoals")->delete();
        $admin = DB::table("users")->orderBy("created_at")->first();
        $banksoals = array_map(function($item) use ($admin) {
            $item["author"] = $admin->id;
            return $item;
        }, $banksoals);
        DB::table("banksoals")->insert($banksoals);
    }

    /**
     * Restore files section
     * @param $files array
     * @return void
     */
    private function restoreFilesSection($files)
    {
        DB::table("files")->delete();
        $new_files = [];
        foreach($files as $file) {
            $new_file = $file;
            Storage::put($file['path'], base64_decode($file['base64']));
            unset($new_file['base64']);
            $new_files[] = $new_file;
        }
        DB::table("files")->insert($new_files);
    }

    /**
     * Restore soals section
     * @param $soals array
     * @return void
     */
    private function restoreSoalSection($soals)
    {
        DB::table("soals")->delete();
        DB::table("soals")->insert($soals);
    }

    /**
     * Restore jawaban_soals section
     * @param $jawaban_soals array
     * @return void
     */
    private function restoreJawabanSoalSection($jawaban_soals)
    {
        DB::table("jawaban_soals")->delete();
        DB::table("jawaban_soals")->insert($jawaban_soals);
    }
}
<?php
namespace App\Services;

use App\ExoBackup;
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
    public const PESERTA_SECTION = "peserta";

    /**
     * Backup exo-cbt data
     * This method will backup jurusan, matpel, directories, banksoals, soals and jawaban soals
     * You have to create app-key for your application
     *
     * @since 3.16.0
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
        $pesertas = DB::table("pesertas")->get();

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
            BackupService::PESERTA_SECTION => $pesertas,
            BackupService::VERSION_SECTION => $this->retreiveVersionSection()
        ]);

        /**
         * Data encryption
         */
        $textInBackup = Crypt::encryptString($textInBackup);

        /**
         * File generation
         */
        $filename = DIRECTORY_SEPARATOR.now()."-exo-backup.exo";
        $path = "public".DIRECTORY_SEPARATOR."backup".$filename;
        DB::table("exo_backups")->insert([
            "filename" => $filename,
            "version" => config("exo.version.code"),
            "detail" => json_encode([
                "jurusan" => $jurusans->count(),
                "matpel" => $matpels->count(),
                "directory" => $directories->count(),
                "banksoal" => $banksoal->count(),
                "file" => $files->count(),
                "soal" => $soals->count(),
                "jawaban_soal" => $jawaban_soals->count(),
                "peserta" => $pesertas->count()
            ]),
            "generated_date" => now()->format('d/m/Y h:i:s A'),
            "bak_type" => ExoBackup::TYPE_BACKUP
        ]);
        Storage::put($path, $textInBackup);
    }

    /**
     * Restore exo-cbt data
     *
     * @since 3.16.0
     */
    public function restore($backupLiteral, $originalName)
    {
        $data_detail = [
            "filename" => $originalName,
            "version" => "Unknown",
            "detail" => [],
            "generated_date" => "Unknown",
            "bak_type" => ExoBackup::TYPE_RESTORE
        ];

        try {
            DB::beginTransaction();
            $textInBackup = Crypt::decryptString($backupLiteral);
            $dataInBackup = json_decode($textInBackup, true);

            $detail = [];
            if(isset($dataInBackup[BackupService::VERSION_SECTION])) {
                $data_detail["version"] = $dataInBackup[BackupService::VERSION_SECTION]["code"];
                $data_detail["generated_date"] = $dataInBackup[BackupService::VERSION_SECTION]["time_generated"];
            }
            if(isset($dataInBackup[BackupService::JURUSAN_SECTIONS])) {
                $detail["jurusan"] = count($dataInBackup[BackupService::JURUSAN_SECTIONS]);
                $this->restoreJurusanSection($dataInBackup[BackupService::JURUSAN_SECTIONS]);
            }
            if(isset($dataInBackup[BackupService::MATPEL_SECTIONS])) {
                $detail["matpel"] = count($dataInBackup[BackupService::MATPEL_SECTIONS]);
                $this->restoreMatpelSection($dataInBackup[BackupService::MATPEL_SECTIONS]);
            }
            if(isset($dataInBackup[BackupService::DIRECTORIES_SECTIONS])) {
                $detail["directory"] = count($dataInBackup[BackupService::DIRECTORIES_SECTIONS]);
                $this->restoreDirectoriesSection($dataInBackup[BackupService::DIRECTORIES_SECTIONS]);
            }
            if(isset($dataInBackup[BackupService::BANKSOAL_SECTION])) {
                $detail["banksoal"] = count($dataInBackup[BackupService::BANKSOAL_SECTION]);
                $this->restoreBanksoalsSection($dataInBackup[BackupService::BANKSOAL_SECTION]);
            }
            if(isset($dataInBackup[BackupService::FILES_SECTION])) {
                $detail["file"] = count($dataInBackup[BackupService::FILES_SECTION]);
                $this->restoreFilesSection($dataInBackup[BackupService::FILES_SECTION]);
            }
            if(isset($dataInBackup[BackupService::SOALS_SECTION])) {
                $detail["soal"] = count($dataInBackup[BackupService::SOALS_SECTION]);
                $this->restoreSoalSection($dataInBackup[BackupService::SOALS_SECTION]);
            }
            if(isset($dataInBackup[BackupService::JAWABAN_SOAL_SECTION])) {
                $detail["jawaban_soal"] = count($dataInBackup[BackupService::JAWABAN_SOAL_SECTION]);
                $this->restoreJawabanSoalSection($dataInBackup[BackupService::JAWABAN_SOAL_SECTION]);
            }
            if(isset($dataInBackup[BackupService::PESERTA_SECTION])) {
                $detail["peserta"] = count($dataInBackup[BackupService::PESERTA_SECTION]);
                $this->restorePesertaSection($dataInBackup[BackupService::PESERTA_SECTION]);
            }
            $data_detail["detail"] = $detail;

            DB::commit();
            return $dataInBackup;
        } catch (Exception $e) {
            DB::rollBack();
            $data_detail["status"] = "FAILED";

            throw $e;
        } finally {
            $data_detail["detail"] = json_encode($data_detail["detail"]);
            DB::table("exo_backups")->insert($data_detail);
        }
    }

    /**
     * Map file base64
     * This file will get base64 file for files data
     * And also do filter if the file not found
     *
     * @since 3.16.0
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
            "time_generated" => now()->format('d/m/Y h:i:s A')
        ];
    }

    /**
     * Restore jurusan section
     *
     * @param $jurusan array
     * @return void
     * @since 3.16.0
     */
    private function restoreJurusanSection($jurusans)
    {
        DB::table("jurusans")->delete();
        DB::table("jurusans")->insert($jurusans);
    }

    /**
     * Restore matpel section
     *
     * @param $matpels array
     * @return void
     * @since 3.16.0
     */
    private function restoreMatpelSection($matpels)
    {
        DB::table("matpels")->delete();
        DB::table("matpels")->insert($matpels);
    }

    /**
     * Restore directories section
     *
     * @param $directories array
     * @return void
     * @since 3.16.0
     */
    private function restoreDirectoriesSection($directories)
    {
        DB::table("directories")->delete();
        DB::table("directories")->insert($directories);
    }

    /**
     * Restore banksoals section
     *
     * @param $banksoals array
     * @return void
     * @since 3.16.0
     */
    private function restoreBanksoalsSection($banksoals)
    {
        DB::table("banksoals")->delete();
        $admin = DB::table("users")->orderBy("created_at")->first();
        $banksoals = array_map(function($item) use ($admin) {
            $item["author"] = $admin->id;
            return $item;
        }, $banksoals);

        $banksoalChuncx = array_chunk($banksoals, 500);
        foreach ($banksoalChuncx as $chuncx) {
            DB::table("banksoals")->insert($chuncx);
        }
    }

    /**
     * Restore files section
     *
     * @param $files array
     * @return void
     * @since 3.16.0
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

        $newfileChuncx = array_chunk($new_files, 500);
        foreach ($newfileChuncx as $chuncx) {
            DB::table("files")->insert($chuncx);
        }
    }

    /**
     * Restore soals section
     *
     * @param $soals array
     * @return void
     * @since 3.16.0
     */
    private function restoreSoalSection($soals)
    {
        DB::table("soals")->delete();

        $soalsChunx = array_chunk($soals, 500);
        foreach ($soalsChunx as $chunx) {
            DB::table("soals")->insert($chunx);
        }
    }

    /**
     * Restore jawaban_soals section
     *
     * @param $jawaban_soals array
     * @return void
     * @since 3.16.0
     */
    private function restoreJawabanSoalSection($jawaban_soals)
    {
        DB::table("jawaban_soals")->delete();

        $jawabanSoalChunx = array_chunk($jawaban_soals, 500);
        foreach ($jawabanSoalChunx as $chunx) {
            DB::table("jawaban_soals")->insert($chunx);
        }
    }

    /**
     * Restore pesertas section
     *
     * @param $pesertas array
     * @return void
     * @since 3.16.0
     */
    private function restorePesertaSection($pesertas)
    {
        DB::table("pesertas")->delete();
        $new_pesertas = [];
        foreach($pesertas as $peserta) {
            $new_peserta = $peserta;
            $new_peserta['api_token'] = null;
            $new_pesertas[] = $new_peserta;
        }

        $pesertaChunx = array_chunk($pesertas,500);
        foreach ($pesertaChunx as $chunx) {
            DB::table("pesertas")->insert($chunx);
        }
    }
}

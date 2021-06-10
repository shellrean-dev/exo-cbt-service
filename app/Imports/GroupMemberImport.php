<?php

namespace App\Imports;

use App\Actions\SendResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Str;

class GroupMemberImport implements ToCollection, WithStartRow
{
    private $group_id;
    
    public function __construct($group_id) {
        $this->group_id = $group_id;
    }

    public function collection(Collection $rows)
    {
        $no_ujians = [];
        foreach($rows as $row) {
            if($row->filter()->isNotEmpty()) {
                array_push($no_ujians, $row[1]);
            }
        }

        $students = DB::table('pesertas')
            ->whereIn('no_ujian', $no_ujians)
            ->select('id')
            ->get();

        $data = [];
        foreach($students as $student) {
            array_push($data, [
                'id'        => Str::uuid()->toString(),
                'group_id'  => $this->group_id,
                'student_id' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        try {
            DB::beginTransaction();
            DB::table('group_members')->insert($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return SendResponse::internalServerError('kesalahan 500.'.$e->getMessage());
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}

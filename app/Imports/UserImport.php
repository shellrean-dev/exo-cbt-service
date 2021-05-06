<?php

namespace App\Imports;

use App\User;
use Illuminate\SUpport\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class UserImport implements ToCollection, WithStartRow
{
    public function collection(Collection $rows)
    {
        $users = [];
        foreach($rows as $row) {
            if ($row->filter()->isNotEmpty()) {
                array_push($users, [
                    'id' => Str::uuid()->toString(),
                    'name' => $row[0],
                    'email' => $row[1],
                    'password' => bcrypt($row[2])
                ]);
            }
        }
        $in_array = DB::table('users')->whereIn('email', array_map(function($item) {
            return $item['email'];
        }, $users))->count();
        if ($in_array) {
            throw new \Exception('duplicate email in excel'); 
        }

        DB::table('users')->insert($users);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            '1' => 'unique:users,email|email'
        ];
    }
}

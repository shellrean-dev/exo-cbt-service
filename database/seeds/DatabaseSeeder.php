<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(AgamaSeeder::class);
        $this->call(TokenSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(InfoFeatureSeeder::class);
        $this->call(JurusanUmum::class);
        $this->call(CreateRoleMenus::class);
    }
}

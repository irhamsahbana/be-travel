<?php

namespace Database\Seeders;

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
        $this->call(CategorySeeder::class);

        $this->DummySeeder();
    }

    private function DummySeeder()
    {
        $this->call(DummyCategorySeeder::class);

        $this->call(DummyCompanySeeder::class);
        $this->call(DummyCompanyAccountSeeder::class);
        $this->call(DummyCompanyPacketTypeSeeder::class);
        $this->call(DummyCompanyServiceSeeder::class);

        $this->call(DummyCompanyPermissionGroupSeeder::class);

        $this->call(DummyBranchSeeder::class);
        $this->call(DummyPersonSeeder::class);
        $this->call(DummyUserSeeder::class);
    }
}

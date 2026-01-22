<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Seed Kriteria Data (harus sesuai urutan karena ada foreign key)
        $this->call([
            KategoriKriteriaSeeder::class,
            SubKriteriaSeeder::class,
            ItemSubKriteriaSeeder::class,
            JenisPembayaranSeeder::class,
        ]);
    }
}

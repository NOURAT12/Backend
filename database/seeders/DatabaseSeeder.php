<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(RoleSeeder::class);
        // $this->call(LevelSeeder::class);
        // $this->call(CourseSeeder::class);
        // $this->call(TimeTableSeeder::class);
        // $this->call(DetailsSeeder::class);
        $this->call(StudentSeeder::class);
    }
}

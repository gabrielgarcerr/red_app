<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Image;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(30)->create()->each(function ($user) {
            Image::factory()->create([
                'imageable_id' => $user->id,
                'imageable_type' => User::class,
            ]);
        });
    }
}
<?php

namespace Database\Seeders;

use App\Models\Recorrido;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecorridoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Recorrido::factory(50)->create();

    }
}

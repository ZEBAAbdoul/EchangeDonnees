<?php

namespace Database\Seeders;

use App\Models\Etat;
use Illuminate\Database\Seeder;

class EtatSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['BF', 'Burkina Faso'], ['ML', 'Mali'], ['NE', 'Niger']] as [$code, $nom]) {
            Etat::firstOrCreate(['code' => $code], ['nom' => $nom]);
        }
    }
}
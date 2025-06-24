<?php

namespace Database\Seeders;

use App\Models\Haberler;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HaberlerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kaç adet veri oluşturacağımızı buradan belirliyoruz.
        $toplamVeri = 250000;
        $parcaBoyutu = 1000;
        $parcaSayisi = ceil($toplamVeri / $parcaBoyutu);

        // Performans için foreign key kontrolünü kapatıyoruz
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Varolan haberleri temizle
        Haberler::truncate();
        
        // Verileri parça parça oluştur
        for ($i = 0; $i < $parcaSayisi; $i++) {
            
            // Son parçada kalan kayıt sayısını hesapla
            if ($i == $parcaSayisi - 1) {
                $olusturulacakVeri = $toplamVeri - ($i * $parcaBoyutu);
            } else {
                $olusturulacakVeri = $parcaBoyutu;
            }
            
            // Factory ile veri oluştur
            Haberler::factory($olusturulacakVeri)->create();
            
            // Her 10 parçada bir hafızayı temizle
            if ($i % 10 == 0) {
                gc_collect_cycles();
            }
        }

        // Foreign key kontrolünü tekrar açıyoruz
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}

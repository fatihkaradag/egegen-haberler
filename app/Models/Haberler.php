<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Haberler extends Model
{
    use HasFactory;

    // Tabloyu belirtiyoruz
    protected $table = 'haberler';

    protected $fillable = [
        'baslik',   // Haber başlığı
        'icerik',   // Haber içeriği
        'resim',    // Haberle ilgili resim dosyasının adı/yolu
    ];


}

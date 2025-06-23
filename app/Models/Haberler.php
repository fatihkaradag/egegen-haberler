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
        'slug',     // URL'de kullanılacak okunabilir metin
        'yayindami' // Haber yayında mı? (true/false)
    ];

    // Belirli alanların veri türlerini belirtiyoruz.
    protected $casts = [
        'yayindami' => 'boolean',
    ];

    // Modelin olaylarını dinlemek için boot metodunu çağırdık.
    protected static function boot()
    {
        parent::boot();
        
        // Yeni bir haber oluşturulurken,
        // Haberin slug alanı başlıktan otomatik olarak oluşturulması için.
        static::creating(function ($haber) {
            $haber->slug = Str::slug($haber->baslik);
        });
    }

}

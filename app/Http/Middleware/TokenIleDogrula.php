<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenIleDogrula
{
    public function handle(Request $request, Closure $next): Response
    {
        // istekten gelen IP adresini alıyoruz
        $ip = $request->ip();
        
        // IP blacklisted mi?
        if (cache()->has("blacklist_$ip")) {
            return response()->json(['error' => 'Bu IP geçici olarak engellendi'], 403);
        }
        
        $token = $request->bearerToken();
        
        // Token doğru ise işleme devam et
        if ($token === '2BH52wAHrAymR7wP3CASt') {
            cache()->forget("fail_count_$ip");
            return $next($request);
        }
        
        // Token yok veya yanlış ise cache ekle
        $failKey = "fail_count_$ip";
        $failCount = cache()->get($failKey, 0) + 1;
        
        // 10 dakika tutması için
        cache()->put($failKey, $failCount, now()->addMinutes(10));
        
        if ($failCount >= 10) {
            cache()->put("blacklist_$ip", true, now()->addMinutes(10));
            return response()->json(['error' => 'Bu IP geçici olarak engellendi'], 403);
        }
        
        return response()->json(['error' => 'Yetkisiz Erişim'], 401);
    }
}
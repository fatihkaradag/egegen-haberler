<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Haberler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HaberlerController extends Controller
{
    /**
     * Tüm haberleri sayfalama ile listele
     */
    public function index(Request $request)
    {   
        // 250.000 veri olacagı için sayfalama eklendi.
        try {

            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);
            
            $haberler = Haberler::orderBy('created_at', 'desc')->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $haberler->items(),
                'pagination' => [
                    'current_page' => $haberler->currentPage(),
                    'last_page' => $haberler->lastPage(),
                    'per_page' => $haberler->perPage(),
                    'total' => $haberler->total(),
                    'from' => $haberler->firstItem(),
                    'to' => $haberler->lastItem(),
                    'has_more_pages' => $haberler->hasMorePages()
                ],
                'message' => 'Haberler başarıyla getirildi.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haberler getirilirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Haber arama fonksiyonu
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2|max:255',
            ], [
                'q.required' => 'Arama terimi zorunludur.',
                'q.string' => 'Arama terimi metin formatında olmalıdır.',
                'q.min' => 'Arama terimi en az 2 karakter olmalıdır.',
                'q.max' => 'Arama terimi en fazla 255 karakter olabilir.',
            ]);

            $arananKelime = $request->get('q');
            $perPage = $request->get('per_page', 15);
            
            $haberler = Haberler::where('baslik', 'LIKE', '%' . $arananKelime . '%')->orderBy('created_at', 'desc')->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $haberler->items(),
                'search_term' => $arananKelime,
                'pagination' => [
                    'last_page' => $haberler->lastPage(),
                    'total' => $haberler->total(),
                ],
                'message' => $haberler->total() > 0 
                    ? $haberler->total() . ' adet haber bulundu.' 
                    : 'Aradığınız kriterlere uygun haber bulunamadı.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arama kriterlerinde hata var.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arama işlemi sırasında bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Yeni haber oluştur
     */
    public function store(Request $request)
    {
        try {
            // Veri doğrulama alanı
            $request->validate([
                'baslik' => 'required|string|max:255',
                'icerik' => 'required|string|min:10',
                'resim' => 'nullable|image|mimes:webp|max:2048'
            ], [
                'baslik.required' => 'Başlık alanı zorunludur.',
                'baslik.string' => 'Başlık metin formatında olmalıdır.',
                'baslik.max' => 'Başlık en fazla 255 karakter olabilir.',
                'icerik.required' => 'İçerik alanı zorunludur.',
                'icerik.string' => 'İçerik metin formatında olmalıdır.',
                'icerik.min' => 'İçerik en az 10 karakter olmalıdır.',
                'resim.image' => 'Yüklenen dosya bir resim olmalıdır.',
                'resim.mimes' => 'Resim sadece webp formatında olmalıdır.',
                'resim.max' => 'Resim boyutu en fazla 2MB olabilir.'
            ]);

            $data = [
                'baslik' => $request->baslik,
                'icerik' => $request->icerik,
            ];

            // Resim yükleme işlemi
            if ($request->hasFile('resim')) {
                $image = $request->file('resim');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                
                $image->storeAs('public/haber', $imageName);
                $data['resim'] = $imageName;
            }

            $haber = Haberler::create($data);

            return response()->json([
                'success' => true,
                'data' => $haber,
                'message' => 'Haber başarıyla oluşturuldu.'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgilerde hata(lar) var.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Belirli bir haberi göster
     */
    public function show(string $id)
    {
        try {

            $haber = Haberler::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $haber,
                'message' => 'Haber başarıyla getirildi.'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Aradığınız haber bulunamadı.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber getirilirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Haberi güncelle
     */
    public function update(Request $request, $id)
    {
        try {
            // İlgili Haberi buluyoruz
            $haber = Haberler::find($id);
            
            if (!$haber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Güncellenecek haber bulunamadı.'
                ], 404);
            }

            // Veri doğrulama
            $request->validate([
                'baslik' => 'required|string|max:255',
                'icerik' => 'required|string|min:10',
                'resim' => 'nullable|image|mimes:webp|max:2048'
            ], [
                'baslik.required' => 'Başlık alanı zorunludur.',
                'baslik.string' => 'Başlık metin formatında olmalıdır.',
                'baslik.max' => 'Başlık en fazla 255 karakter olabilir.',
                'icerik.required' => 'İçerik alanı zorunludur.',
                'icerik.string' => 'İçerik metin formatında olmalıdır.',
                'icerik.min' => 'İçerik en az 10 karakter olmalıdır.',
                'resim.image' => 'Yüklenen dosya bir resim olmalıdır.',
                'resim.mimes' => 'Resim sadece webp formatında olmalıdır.',
                'resim.max' => 'Resim boyutu en fazla 2MB olabilir.'
            ]);

            $data = [
                'baslik' => $request->baslik,
                'icerik' => $request->icerik,
            ];

            // Yeni resim yükleme işlemi
            if ($request->hasFile('resim')) {
                if ($haber->resim && Storage::exists('public/haber/' . $haber->resim)) {
                    Storage::delete('public/haber/' . $haber->resim);
                }

                // Yeni resmi kaydediyoruz
                $image = $request->file('resim');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/haber', $imageName);
                $data['resim'] = $imageName;
            }

            $haber->update($data);

            return response()->json([
                'success' => true,
                'data' => $haber->fresh(), // Güncellenmiş veriyi getiriyoruz
                'message' => 'Haber başarıyla güncellendi.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgilerde hata var.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber güncellenirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Haberi sil
     */
    public function destroy(string $id)
    {
        try {
            $haber = Haberler::findOrFail($id);

            // Zaten bir resim varsa sil
            if ($haber->resim && Storage::exists('public/haber/' . $haber->resim)) {
                Storage::delete('public/haber/' . $haber->resim);
            }

            $haber->delete();

            return response()->json([
                'success' => true,
                'message' => 'Haber başarıyla silindi.'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Silinecek haber bulunamadı.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber silinirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
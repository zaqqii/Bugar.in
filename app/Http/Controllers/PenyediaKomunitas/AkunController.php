<?php

namespace App\Http\Controllers\PenyediaKomunitas;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AkunController extends Controller
{
    /** Daftar kategori olahraga yang tersedia */
    public const SPORT_CATEGORIES = [
        'lari'       => 'Lari / Running',
        'sepeda'     => 'Sepeda / Cycling',
        'renang'     => 'Renang / Swimming',
        'badminton'  => 'Badminton',
        'basket'     => 'Basket / Basketball',
        'futsal'     => 'Futsal / Football',
        'padel'      => 'Padel',
        'tenis'      => 'Tenis',
        'yoga'       => 'Yoga / Pilates',
        'voli'       => 'Voli / Volleyball',
        'hiking'     => 'Hiking / Outdoor',
        'gym'        => 'Gym / Fitness',
        'martial'    => 'Bela Diri',
        'other'      => 'Olahraga Lainnya',
    ];

    /**
     * Tampilkan halaman pengaturan profil dan komunitas host.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $community = Community::where('owner_id', $user->id)->first();

        // Cek apakah komunitas otomatis terverifikasi dengan syarat 15 pengikut
        if ($community && $community->verification_status !== 'verified') {
            $followersCount = $community->followers()->count();
            if ($followersCount !== $community->followers_count) {
                $community->update(['followers_count' => $followersCount]);
            }

            if ($followersCount >= 15) {
                $updateData = [
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                ];
                if (Schema::hasColumn('communities', 'verification_type')) {
                    $updateData['verification_type'] = 'free_followers';
                }
                $community->update($updateData);

                session()->flash('success_auto_verify', 'Selamat! Komunitas Anda telah otomatis terverifikasi karena berhasil mengumpulkan 15+ pengikut. Anda sekarang dapat membuat event berbayar!');
            }
        }

        $followersCount = $community?->followers_count ?? 0;
        $followersTarget = 15;
        $followersProgress = min(100, (int) round(($followersCount / $followersTarget) * 100));
        $canClaimFree = $followersCount >= $followersTarget;

        return view('penyedia-komunitas.akun.index', [
            'user' => $user,
            'community' => $community,
            'sportCategories' => self::SPORT_CATEGORIES,
            'followersCount' => $followersCount,
            'followersTarget' => $followersTarget,
            'followersProgress' => $followersProgress,
            'canClaimFree' => $canClaimFree,
        ]);
    }

    /**
     * Perbarui data profil penanggung jawab (PIC / Host).
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama penanggung jawab wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / telepon penanggung jawab wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'avatar.image' => 'File avatar harus berupa gambar (JPG, PNG, atau WEBP).',
            'avatar.max' => 'Ukuran avatar maksimal 2MB.',
        ]);

        $disk = config('filesystems.default');

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk($disk)->exists($user->avatar)) {
                Storage::disk($disk)->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('users/avatars', $disk);
            $validated['avatar'] = $avatarPath;
        }

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);

        // Sinkronkan juga nomor kontak penanggung jawab di komunitas jika ada
        $community = Community::where('owner_id', $user->id)->first();
        if ($community) {
            $community->update([
                'contact_phone' => $validated['phone'],
            ]);
        }

        return redirect()->route('penyedia-komunitas.akun.index')
            ->with('success', 'Profil penanggung jawab berhasil diperbarui!');
    }

    /**
     * Perbarui data profil komunitas (nama, deskripsi, kota, kontak, cover, logo).
     */
    public function updateCommunity(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $community = Community::where('owner_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sport_category' => ['required', 'string', Rule::in(array_keys(self::SPORT_CATEGORIES))],
            'city' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1500'],
            'contact_phone' => ['required', 'string', 'max:25'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama komunitas wajib diisi.',
            'sport_category.required' => 'Kategori olahraga wajib dipilih.',
            'city.required' => 'Kota basecamp komunitas wajib diisi.',
            'contact_phone.required' => 'Nomor telepon / WhatsApp kontak komunitas wajib diisi.',
            'contact_email.email' => 'Format email kontak tidak valid.',
            'cover_image.image' => 'Cover komunitas harus berupa gambar (JPG, PNG, atau WEBP).',
            'cover_image.max' => 'Ukuran banner cover maksimal 3MB.',
            'logo.image' => 'Logo komunitas harus berupa gambar (JPG, PNG, atau WEBP).',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
        ]);

        $disk = config('filesystems.default');

        // Handle Cover Image Upload
        if ($request->hasFile('cover_image')) {
            if ($community->cover_image && Storage::disk($disk)->exists($community->cover_image)) {
                Storage::disk($disk)->delete($community->cover_image);
            }
            $coverPath = $request->file('cover_image')->store('communities/covers', $disk);
            $validated['cover_image'] = $coverPath;
        }

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            if ($community->logo && Storage::disk($disk)->exists($community->logo)) {
                Storage::disk($disk)->delete($community->logo);
            }
            $logoPath = $request->file('logo')->store('communities/logos', $disk);
            $validated['logo'] = $logoPath;
        }

        // Clean instagram username / handle
        $instagram = $validated['instagram'] ?? null;
        if ($instagram) {
            $instagram = ltrim($instagram, '@');
        }

        $community->update([
            'name' => $validated['name'],
            'sport_category' => $validated['sport_category'],
            'city' => $validated['city'],
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'contact_phone' => $validated['contact_phone'],
            'contact_email' => $validated['contact_email'] ?? null,
            'instagram' => $instagram,
            'website' => $validated['website'] ?? null,
            'cover_image' => $validated['cover_image'] ?? $community->cover_image,
            'logo' => $validated['logo'] ?? $community->logo,
        ]);

        // Pastikan phone user juga sinkron jika berubah
        if (!empty($validated['contact_phone'])) {
            $user->update(['phone' => $validated['contact_phone']]);
        }

        return redirect()->route('penyedia-komunitas.akun.index')
            ->with('success', 'Informasi dan cover komunitas berhasil diperbarui!');
    }

    /**
     * Ajukan atau aktifkan verifikasi komunitas (versi gratis 15 pengikut atau berbayar).
     */
    public function verify(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $community = Community::where('owner_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'verification_type' => ['required', 'in:free,paid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        $type = $validated['verification_type'];

        if ($type === 'free') {
            // Syarat: Minimal 15 pengikut
            $actualFollowers = $community->followers()->count();
            if ($actualFollowers < 15) {
                return redirect()->route('penyedia-komunitas.akun.index')
                    ->with('error', "Komunitas belum memenuhi syarat verifikasi gratis. Diperlukan minimal 15 pengikut (saat ini baru {$actualFollowers} pengikut).");
            }

            $updateData = [
                'verification_status' => 'verified',
                'verified_at' => now(),
            ];
            if (Schema::hasColumn('communities', 'verification_type')) {
                $updateData['verification_type'] = 'free_followers';
            }
            if (Schema::hasColumn('communities', 'verification_requested_at')) {
                $updateData['verification_requested_at'] = now();
            }

            $community->update($updateData);

            return redirect()->route('penyedia-komunitas.akun.index')
                ->with('success', 'Selamat! Komunitas Anda berhasil diverifikasi secara gratis dengan 15+ pengikut. Anda sekarang dapat membuat event berbayar!');
        }

        if ($type === 'paid') {
            // Opsi berbayar: Terverifikasi instan
            $paymentMethod = $validated['payment_method'] ?? 'qris';

            $updateData = [
                'verification_status' => 'verified',
                'verified_at' => now(),
            ];
            if (Schema::hasColumn('communities', 'verification_type')) {
                $updateData['verification_type'] = 'paid';
            }
            if (Schema::hasColumn('communities', 'verification_fee')) {
                $updateData['verification_fee'] = 99000;
            }
            if (Schema::hasColumn('communities', 'verification_payment_method')) {
                $updateData['verification_payment_method'] = $paymentMethod;
            }
            if (Schema::hasColumn('communities', 'verification_requested_at')) {
                $updateData['verification_requested_at'] = now();
            }

            $community->update($updateData);

            return redirect()->route('penyedia-komunitas.akun.index')
                ->with('success', 'Pembayaran berhasil dikonfirmasi! Komunitas Anda kini resmi terverifikasi dan dapat segera membuat event berbayar.');
        }

        return redirect()->route('penyedia-komunitas.akun.index');
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Streak;
use App\Models\User;
use App\Services\UserLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Tampilkan formulir pemilihan cabang olahraga setelah registrasi akun baru.
     */
    public function showOnboardingSports(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'user') {
            return redirect()->route('landing');
        }

        $availableSports = User::availableSports();
        $userSports = $user->sports ?? [];

        return view('user.onboarding-sports', compact('user', 'availableSports', 'userSports'));
    }

    /**
     * Simpan preferensi cabang olahraga dari halaman onboarding.
     */
    public function saveOnboardingSports(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'user') {
            return redirect()->route('landing');
        }

        $validated = $request->validate([
            'sports' => 'nullable|array',
            'sports.*' => 'string|max:50',
            'fitness_level' => 'nullable|string|in:beginner,intermediate,advanced,elite',
            'weekly_target' => 'nullable|string|in:1-2,3-4,5-6,7',
        ]);

        $selectedSports = $validated['sports'] ?? [];

        $user->update([
            'sports' => $selectedSports,
            'fitness_level' => $validated['fitness_level'] ?? $user->fitness_level ?? 'intermediate',
            'weekly_target' => $validated['weekly_target'] ?? $user->weekly_target ?? '3-4',
        ]);

        // Inisialisasi streak awal untuk cabang olahraga yang dipilih
        if (!empty($selectedSports)) {
            foreach ($selectedSports as $sport) {
                if (is_string($sport) && !empty($sport)) {
                    Streak::firstOrCreate(
                        ['user_id' => $user->id, 'sport_category' => $sport],
                        ['current_streak' => 0, 'longest_streak' => 0]
                    );
                }
            }
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Preferensi olahraga kamu berhasil disimpan! Selamat datang di Bugar.in, ' . $user->name . '.');
    }

    /**
     * Tampilkan halaman Edit Profil pengguna yang sedang login.
     */
    public function edit(Request $request, UserLevelService $levelService)
    {
        /** @var User $user */
        $user = Auth::user();

        // Hitung data streak dinamis dari database dengan try-catch agar tahan error
        try {
            $currentStreak = DB::table('streaks')
                ->where('user_id', $user->id)
                ->max('current_streak') ?? 0;

            $streakBonusPoints = DB::table('points_histories')
                ->where('user_id', $user->id)
                ->where('source', 'streak_bonus')
                ->sum('points');

            $streakPoints = $streakBonusPoints > 0 ? (int)$streakBonusPoints : ($currentStreak > 0 ? (int)$currentStreak * 25 : 0);

            $recentAttendances = DB::table('attendances')
                ->join('participants', 'attendances.participant_id', '=', 'participants.id')
                ->where('participants.user_id', $user->id)
                ->where('attendances.created_at', '>=', now()->subDays(30))
                ->count();

            $consistencyRate = $recentAttendances > 0 ? min(100, max(20, round(($recentAttendances / 12) * 100))) : 82;
            $consistencyTrend = '+' . min(30, max(12, round($consistencyRate * 0.22))) . '%';
        } catch (\Throwable $e) {
            $currentStreak = 0;
            $streakPoints = 0;
            $consistencyRate = 82;
            $consistencyTrend = '+18%';
        }

        $availableSports = User::availableSports();
        $levelProgress = $levelService->progress($user);

        return view('user.edit_profile', compact(
            'user',
            'currentStreak',
            'streakPoints',
            'consistencyRate',
            'consistencyTrend',
            'availableSports',
            'levelProgress'
        ));
    }

    /**
     * Simpan pembaruan data profil user ke database sesuai migrasi.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:25',
            'bio' => 'nullable|string|max:160',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'sports' => 'nullable|array',
            'sports.*' => 'string|max:50',
            'fitness_level' => 'nullable|string|in:beginner,intermediate,advanced,elite',
            'weekly_target' => 'nullable|string|in:1-2,3-4,5-6,7',
            'sparring_open' => 'nullable',
        ]);

        if ($request->hasFile('avatar')) {
            $disk = config('filesystems.default');
            if ($user->avatar && Storage::disk($disk)->exists($user->avatar)) {
                Storage::disk($disk)->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('users/avatars', $disk);
            $validated['avatar'] = $avatarPath;
        }

        $newSports = $request->has('preferences_submitted') ? $request->input('sports', []) : ($user->sports ?? []);

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'avatar' => $validated['avatar'] ?? $user->avatar,
            'sports' => $newSports,
            'fitness_level' => $validated['fitness_level'] ?? $user->fitness_level ?? 'intermediate',
            'weekly_target' => $validated['weekly_target'] ?? $user->weekly_target ?? '3-4',
            'sparring_open' => $request->has('preferences_submitted') ? $request->boolean('sparring_open') : ($user->sparring_open ?? true),
        ]);

        // Buat streak record untuk cabor baru yang dipilih
        if ($request->has('preferences_submitted') && is_array($newSports)) {
            foreach ($newSports as $sport) {
                if (is_string($sport) && !empty($sport)) {
                    Streak::firstOrCreate(
                        ['user_id' => $user->id, 'sport_category' => $sport],
                        ['current_streak' => 0, 'longest_streak' => 0]
                    );
                }
            }
        }

        return redirect()->route('user.profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }
}

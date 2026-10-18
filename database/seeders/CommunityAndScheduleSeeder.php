<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Community;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class CommunityAndScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $communitiesData = [
            [
                'name' => 'South Jakarta Night Runners',
                'slug' => 'south-jakarta-night-runners',
                'sport_category' => 'Lari & Track',
                'city' => 'Jakarta Selatan',
                'location' => 'Senayan & GBK, Jaksel',
                'description' => 'Lari santai melepas penat sepulang kantor bersama ratusan penggiat urban.',
                'members_count' => 2150,
                'cover_image' => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'tuesday', 'title' => 'Sesi Lari Malam', 'time' => '19:30:00']
            ],
            [
                'name' => 'Jakarta Padel Masters',
                'slug' => 'jakarta-padel-masters',
                'sport_category' => 'Padel & Tenis',
                'city' => 'Jakarta Selatan',
                'location' => 'TB Simatupang, Jaksel',
                'description' => 'Komunitas padel intensif untuk match doubles mingguan. Sesi warm-up teknik...',
                'members_count' => 480,
                'cover_image' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'saturday', 'title' => 'Weekend Padel Match', 'time' => '08:00:00']
            ],
            [
                'name' => 'Tebet Badminton Club',
                'slug' => 'tebet-badminton-club',
                'sport_category' => 'Badminton',
                'city' => 'Jakarta Selatan',
                'location' => 'Tebet, Jaksel',
                'description' => 'Sparing seru 3 lapangan karpet vinyl standar PBSI. Sistem rolling rally point...',
                'members_count' => 890,
                'cover_image' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'wednesday', 'title' => 'Sparing Rabu Malam', 'time' => '20:00:00']
            ],
            [
                'name' => 'Bandung Roadbike Community',
                'slug' => 'bandung-roadbike-community',
                'sport_category' => 'Sepeda & Roadbike',
                'city' => 'Bandung',
                'location' => 'Dago Atas, Bandung',
                'description' => 'Gowes menanjak menyusuri udara sejuk Bandung utara dengan pengawalan.',
                'members_count' => 1340,
                'cover_image' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'sunday', 'title' => 'Gowes Climax Dago', 'time' => '06:00:00']
            ],
            [
                'name' => 'Surabaya Mini Soccer League',
                'slug' => 'surabaya-mini-soccer-league',
                'sport_category' => 'Futsal & Mini Soccer',
                'city' => 'Surabaya',
                'location' => 'Mayjen Sungkono, Sby',
                'description' => 'Ajang fun match mini soccer rutin tiap pekan untuk menjaga kebugaran dan jejaring.',
                'members_count' => 1650,
                'cover_image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'friday', 'title' => 'Fun Match Jumat Malam', 'time' => '20:00:00']
            ],
            [
                'name' => 'Malang Calisthenics Family',
                'slug' => 'malang-calisthenics-family',
                'sport_category' => 'Gym & Calisthenics',
                'city' => 'Malang',
                'location' => 'Stadion Gajayana, Malang',
                'description' => 'Latihan beban tubuh (bodyweight training) bersama dari level basic hingga advanced.',
                'members_count' => 720,
                'cover_image' => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'thursday', 'title' => 'Bar Workout Sesi', 'time' => '16:00:00']
            ],
            [
                'name' => 'Bali Sunset Yoga Tribe',
                'slug' => 'bali-sunset-yoga-tribe',
                'sport_category' => 'Yoga & Stretching',
                'city' => 'Denpasar',
                'location' => 'Canggu Beach, Bali',
                'description' => 'Peregangan dan yoga di tepi pantai menyambut matahari terbenam.',
                'members_count' => 950,
                'cover_image' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'monday', 'title' => 'Sunset Flow Yoga', 'time' => '17:30:00']
            ],
            [
                'name' => 'Jogja Basketball Mob',
                'slug' => 'jogja-basketball-mob',
                'sport_category' => 'Basket',
                'city' => 'Yogyakarta',
                'location' => 'GOR UNY, Yogyakarta',
                'description' => 'Open court 5on5 half-court dan full-court game santai tapi kompetitif.',
                'members_count' => 610,
                'cover_image' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'tuesday', 'title' => 'Night Hoops', 'time' => '19:00:00']
            ],
            [
                'name' => 'Surabaya Urban Swimmers',
                'slug' => 'surabaya-urban-swimmers',
                'sport_category' => 'Renang',
                'city' => 'Surabaya',
                'location' => 'Kolam Renang Graha Famili',
                'description' => 'Latihan teknik renang gaya bebas dan endurance bersama pelatih pendamping.',
                'members_count' => 430,
                'cover_image' => 'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'saturday', 'title' => 'Morning Laps', 'time' => '07:00:00']
            ],
            [
                'name' => 'Jakarta Senayan Tennis Hub',
                'slug' => 'jakarta-senayan-tennis-hub',
                'sport_category' => 'Padel & Tenis',
                'city' => 'Jakarta Pusat',
                'location' => 'Lapangan Tenis Senayan',
                'description' => 'Hitting session dan sparing reguler untuk pencinta tenis lapangan hijau.',
                'members_count' => 890,
                'cover_image' => 'https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=600&q=80',
                'schedule' => ['day' => 'thursday', 'title' => 'Evening Tennis Rally', 'time' => '18:30:00']
            ],
        ];

        foreach ($communitiesData as $data) {
            $scheduleInfo = $data['schedule'];
            unset($data['schedule']);

            // Masukkan data komunitas
            $community = Community::create(array_merge($data, [
                'owner_id' => 1,
                'verification_status' => 'verified',
                'is_active' => true,
            ]));

            // Masukkan jadwal latihannya
            Schedule::create([
                'community_id' => $community->id,
                'title' => $scheduleInfo['title'],
                'day_of_week' => $scheduleInfo['day'],
                'start_time' => $scheduleInfo['time'],
                'end_time' => '21:00:00',
                'location' => $data['location'],
                'status' => 'open',
            ]);
        }
    }
}

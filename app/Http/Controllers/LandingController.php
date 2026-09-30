<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display the landing page.
     * Shows featured communities, upcoming events, and platform stats.
     */
    public function index()
    {
        // Static data for landing page (will be replaced with real DB data later)
        $stats = [
            'members'     => '48,000+',
            'communities' => '1,250+',
            'events'      => '320+',
            'rating'      => '4.9',
        ];

        $popularCommunities = $this->getFeaturedCommunities();
        $upcomingEvents     = $this->getUpcomingEvents();
        $leaderboard        = $this->getMiniLeaderboard();

        return view('landing', compact('stats', 'popularCommunities', 'upcomingEvents', 'leaderboard'));
    }

    /**
     * Featured communities data for landing page showcase.
     */
    private function getFeaturedCommunities(): array
    {
        return [
            [
                'id'           => 1,
                'name'         => 'Jakarta Sunday Runners',
                'sport'        => 'Running',
                'sport_key'    => 'running',
                'location'     => 'GBK, Jakarta Pusat',
                'members'      => '2.1K',
                'followers'    => '5,200',
                'rating'       => '4.8',
                'schedule'     => 'Minggu, 05:30 WIB',
                'fee'          => 'Gratis',
                'cover'        => 'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=400&q=80',
                'description'  => 'Komunitas lari ramah pemula. Fun run setiap minggu pagi di GBK.',
            ],
            [
                'id'           => 2,
                'name'         => 'Sudirman Padel Society',
                'sport'        => 'Padel',
                'sport_key'    => 'padel',
                'location'     => 'Sudirman, Jakarta',
                'members'      => '800',
                'followers'    => '1,800',
                'rating'       => '4.9',
                'schedule'     => 'Sabtu & Rabu, 19:00',
                'fee'          => 'Rp 50K',
                'cover'        => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=400&q=80',
                'description'  => 'Bermain padel bersama, dari beginner sampai advanced level.',
            ],
            [
                'id'           => 3,
                'name'         => 'Bandung Peloton Squad',
                'sport'        => 'Cycling',
                'sport_key'    => 'cycling',
                'location'     => 'Dago, Bandung',
                'members'      => '960',
                'followers'    => '3,400',
                'rating'       => '4.7',
                'schedule'     => 'Minggu, 06:00 WIB',
                'fee'          => 'Gratis',
                'cover'        => 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?w=400&q=80',
                'description'  => 'Gowes bareng dari Dago sampai Lembang setiap minggu.',
            ],
            [
                'id'           => 4,
                'name'         => 'Depok Smashers Club',
                'sport'        => 'Badminton',
                'sport_key'    => 'badminton',
                'location'     => 'Margonda, Depok',
                'members'      => '1,140',
                'followers'    => '2,900',
                'rating'       => '4.6',
                'schedule'     => 'Selasa & Jumat, 19:30',
                'fee'          => 'Rp 35K',
                'cover'        => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=400&q=80',
                'description'  => 'Komunitas bulutangkis aktif bagi penggemar olahraga raket.',
            ],
        ];
    }

    /**
     * Upcoming events data for landing page.
     */
    private function getUpcomingEvents(): array
    {
        return [
            [
                'id'          => 1,
                'title'       => 'GBK Night Pace 5.8K',
                'community'   => 'Jakarta Sunday Runners',
                'type'        => 'fun_match',
                'date'        => 'Sabtu, 20 Okt • 17:30–19:30',
                'location'    => 'GBK Outdoor Track, Jakarta',
                'fee'         => 'Gratis',
                'fee_value'   => 0,
                'slots_used'  => 75,
                'slots_total' => 100,
                'featured'    => false,
            ],
            [
                'id'          => 2,
                'title'       => 'Beginner Americano Mini Tourney',
                'community'   => 'Sudirman Padel Society',
                'type'        => 'tournament',
                'date'        => 'Rabu, 23 Okt • 19:00–22:00',
                'location'    => 'Pacific Court GOR, Jakarta',
                'fee'         => 'Rp 45.000',
                'fee_value'   => 45000,
                'slots_used'  => 14,
                'slots_total' => 16,
                'featured'    => true,
            ],
            [
                'id'          => 3,
                'title'       => 'Sunday Double Smash Series',
                'community'   => 'Depok Smashers Club',
                'type'        => 'fun_match',
                'date'        => 'Minggu, 26 Okt • 07:30–11:30',
                'location'    => 'GOR Kota Jaya, Margonda',
                'fee'         => 'Rp 35.000',
                'fee_value'   => 35000,
                'slots_used'  => 32,
                'slots_total' => 48,
                'featured'    => false,
            ],
        ];
    }

    /**
     * Mini leaderboard for homepage showcase.
     */
    private function getMiniLeaderboard(): array
    {
        return [
            [
                'rank'        => 1,
                'name'        => 'Jakarta Sunday Runners',
                'meta'        => '5,200 pts | 1.2K anggota',
                'points_text' => '+892 pts',
                'rank_key'    => 'gold',
            ],
            [
                'rank'        => 2,
                'name'        => 'Sudirman Padel Society',
                'meta'        => '3,950 pts | 800 anggota',
                'points_text' => '+540 pts',
                'rank_key'    => 'silver',
            ],
        ];
    }
}

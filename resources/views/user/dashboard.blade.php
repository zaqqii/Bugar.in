@extends('layouts.user')

@section('title', 'Dashboard Pengguna - Bugar.in')

@section('content')
    <div x-data="{
        filterCategory: '',
        filterCity: '',
        searchQuery: '',
        komunitasSubTab: 'komunitas', // 'komunitas' | 'jadwal'
        agendaItems: @js($agendaItems),
        joinedScheduleIds: @js($joinedScheduleIds),
        scheduleDetailModalOpen: false,
        modalScheduleItem: null,
        openScheduleDetail(item) {
            if (!item) return;
            this.modalScheduleItem = { ...item };
            this.scheduleDetailModalOpen = true;
            if (item.kind === 'schedule') {
                const sId = item.raw_id || item.id.replace('schedule-', '');
                fetch(`/user/schedules/${sId}/availability`)
                    .then(res => res.json())
                    .then(res => {
                        if (this.modalScheduleItem) {
                            this.modalScheduleItem.filled = res.registered_count;
                            this.modalScheduleItem.remaining = res.remaining;
                            this.modalScheduleItem.capacity = res.capacity;
                            this.modalScheduleItem.is_full = res.is_full;
                        }
                    })
                    .catch(() => {});
            }
        },
        agendaView: 'calendar',
        agendaTypeFilter: 'all',
        agendaCommunityFilter: 'all',
        agendaScheduleFilter: 'all', // 'all' | 'popular' | 'mine'
        agendaMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        agendaSelectedDate: new Date().toLocaleDateString('sv-SE'),
        agendaSelectedId: null,
        userParticipantKeys: @js($userParticipants->keys()->values() ?? collect()),

        agendaDateKey(date) {
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        },
        agendaCalendarCells() {
            const first = new Date(this.agendaMonth.getFullYear(), this.agendaMonth.getMonth(), 1);
            const start = new Date(first);
            start.setDate(first.getDate() - ((first.getDay() + 6) % 7));
            return Array.from({ length: 42 }, (_, index) => {
                const date = new Date(start);
                date.setDate(start.getDate() + index);
                return date;
            });
        },
        agendaMatchesFilters(item) {
            if (this.agendaTypeFilter !== 'all' && item.type !== this.agendaTypeFilter) return false;
            if (this.agendaCommunityFilter !== 'all' && item.community !== this.agendaCommunityFilter) return false;
            if (this.agendaScheduleFilter === 'mine') {
                return this.isJoinedItem(item);
            }
            if (this.agendaScheduleFilter === 'popular') {
                return (item.capacity > 0 && (item.filled / item.capacity) >= 0.5) || item.filled >= 5;
            }
            return true;
        },
        agendaForDate(date) {
            const key = this.agendaDateKey(date);
            return this.agendaItems.filter(item => this.agendaMatchesFilters(item) && item.date === key);
        },
        get agendaSelectedItem() {
            return this.agendaItems.find(item => item.id === this.agendaSelectedId && this.agendaMatchesFilters(item)) ||
                this.agendaForDate(new Date(`${this.agendaSelectedDate}T00:00:00`))[0] || null;
        },
        agendaSelectDate(date) {
            this.agendaSelectedDate = this.agendaDateKey(date);
            const firstItem = this.agendaForDate(date)[0];
            this.agendaSelectedId = firstItem ? firstItem.id : null;
        },
        agendaShiftMonth(amount) {
            this.agendaMonth = new Date(this.agendaMonth.getFullYear(), this.agendaMonth.getMonth() + amount, 1);
        },
        get agendaCommunities() {
            return [...new Set(this.agendaItems.map(item => item.community))];
        },
        get agendaUpcoming() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const upcoming = [];
            for (let offset = 0; offset < 60; offset++) {
                const date = new Date(today);
                date.setDate(today.getDate() + offset);
                this.agendaForDate(date).forEach(item => upcoming.push({ ...item, occurrenceDate: this.agendaDateKey(date) }));
            }
            return upcoming;
        },
        isJoinedItem(item) {
            if (!item) return false;
            if (item.kind === 'schedule') {
                const sId = parseInt(item.raw_id || item.id.replace('schedule-', ''));
                return this.joinedScheduleIds.includes(sId);
            }
            const realId = item.id.replace('event-', '');
            return this.userParticipantKeys.includes(`App\\Models\\Event-${realId}`);
        },
        leaderboardMetric: 'points',
        activityDetailModalOpen: false,
        selectedMembershipDetail: null,
        joinedActivitiesByCommunity: @js($joinedActivitiesByCommunity),
        openMembershipDetail(communityId, communityName, sportCategory, city, membershipStatus) {
            const activities = this.joinedActivitiesByCommunity[communityId] || [];
            this.selectedMembershipDetail = {
                communityId,
                communityName,
                sportCategory,
                city,
                membershipStatus,
                activities,
                activeCount: activities.filter(a => a.is_active_session).length,
                attendedCount: activities.filter(a => a.status_raw === 'attended').length,
            };
            this.activityDetailModalOpen = true;
        },
        leaderboardUsers: @js($leaderboardUsers->map(fn($rankedUser) => [
            'id' => $rankedUser->id,
            'name' => $rankedUser->name,
            'username' => $rankedUser->username,
            'total_points' => (int) $rankedUser->total_points,
            'attended_sessions_count' => (int) $rankedUser->attended_sessions_count,
            'current_streak' => (int) ($rankedUser->current_streak ?? 0),
            'longest_streak' => (int) ($rankedUser->longest_streak ?? 0),
            'is_current_user' => $rankedUser->is_current_user,
        ])->values()),
        get leaderboardRows() {
            const metric = { points: 'total_points', sessions: 'attended_sessions_count', streak: 'longest_streak' }[this.leaderboardMetric];
            return [...this.leaderboardUsers]
                .sort((a, b) => (b[metric] - a[metric]) || (b.total_points - a.total_points) || a.name.localeCompare(b.name, 'id'))
                .map((person, index) => ({ ...person, rank: index + 1 }));
        },
        get leaderboardTopThree() { return this.leaderboardRows.slice(0, 3); },
        get leaderboardCurrentUser() { return this.leaderboardRows.find(person => person.is_current_user) || null; },

        // BMI Calculator state & Kemenkes RI gender-aware logic
        bmiHeight: 170,
        bmiWeight: 65,
        bmiGender: 'l',
        bmiAge: 24,
        bmiSavedFlash: false,
        bmiHistory: (() => {
            try { return JSON.parse(localStorage.getItem('bugarin_bmi_history_v1') || '[]'); } catch (e) { return []; }
        })(),

        bmiBlockInvalidKeys(event, allowDecimal = true) {
            if (['-', '+', 'e', 'E'].includes(event.key)) {
                event.preventDefault();
                return;
            }
            if (!allowDecimal && ['.', ','].includes(event.key)) {
                event.preventDefault();
            }
        },

        bmiSanitizeField(field, event, allowDecimal = true) {
            let raw = event && event.target ? String(event.target.value) : String(this[field] ?? '');
            if (raw.includes('-')) {
                raw = raw.replace(/-/g, '');
                if (event && event.target) event.target.value = raw;
            }
            if (raw === '') {
                this[field] = '';
                return;
            }
            const parsed = allowDecimal ? parseFloat(raw) : parseInt(raw, 10);
            if (Number.isNaN(parsed)) {
                this[field] = '';
                return;
            }
            this[field] = parsed < 0 ? Math.abs(parsed) : parsed;
        },

        bmiStep(field, delta, min, max, decimals = 0) {
            const raw = parseFloat(this[field]);
            const current = (Number.isNaN(raw) || raw <= 0) ? min : raw;
            const clamped = Math.min(max, Math.max(min, current + delta));
            this[field] = Number(clamped.toFixed(decimals));
        },

        bmiResetDefault() {
            if (this.bmiGender === 'l') {
                this.bmiHeight = 170;
                this.bmiWeight = 65;
                this.bmiAge = 24;
            } else {
                this.bmiHeight = 160;
                this.bmiWeight = 54;
                this.bmiAge = 24;
            }
        },

        get bmiErrors() {
            const hRaw = this.bmiHeight;
            const wRaw = this.bmiWeight;
            const aRaw = this.bmiAge;
            const h = Number(hRaw);
            const w = Number(wRaw);
            const a = Number(aRaw);

            let height = '';
            if (hRaw === '' || hRaw === null || Number.isNaN(h)) {
                height = 'Tinggi badan wajib diisi (50–250 cm).';
            } else if (h <= 0) {
                height = 'Tinggi badan tidak boleh 0 atau minus.';
            } else if (h < 50 || h > 250) {
                height = 'Tinggi badan harus antara 50 hingga 250 cm.';
            }

            let weight = '';
            if (wRaw === '' || wRaw === null || Number.isNaN(w)) {
                weight = 'Berat badan wajib diisi (10–300 kg).';
            } else if (w <= 0) {
                weight = 'Berat badan tidak boleh 0 atau minus.';
            } else if (w < 10 || w > 300) {
                weight = 'Berat badan harus antara 10 hingga 300 kg.';
            }

            let age = '';
            if (aRaw === '' || aRaw === null || Number.isNaN(a)) {
                age = 'Usia wajib diisi (10–100 tahun).';
            } else if (a <= 0) {
                age = 'Usia tidak boleh 0 atau minus.';
            } else if (a < 10 || a > 100) {
                age = 'Usia harus antara 10 hingga 100 tahun.';
            }

            return { height, weight, age };
        },

        get bmiIsValid() {
            return !this.bmiErrors.height && !this.bmiErrors.weight && !this.bmiErrors.age;
        },

        get bmiIsUnder18() {
            return !this.bmiErrors.age && Number(this.bmiAge) < 18;
        },

        get bmiThresholds() {
            if (this.bmiGender === 'p') {
                return {
                    genderLabel: 'Perempuan',
                    underMax: 17.0,
                    normalMin: 17.0,
                    normalMax: 23.0,
                    overMax: 27.0,
                    underText: '< 17.0',
                    normalText: '17.0 – 23.0',
                    overText: '23.1 – 27.0',
                    obeseText: '> 27.0',
                    waistLimit: '< 80 cm',
                    brocaFormulaText: '(TB - 100) - 15%',
                };
            }
            return {
                genderLabel: 'Laki-laki',
                underMax: 18.0,
                normalMin: 18.0,
                normalMax: 25.0,
                overMax: 27.0,
                underText: '< 18.0',
                normalText: '18.0 – 25.0',
                overText: '25.1 – 27.0',
                obeseText: '> 27.0',
                waistLimit: '< 90 cm',
                brocaFormulaText: '(TB - 100) - 10%',
            };
        },

        get bmiNumeric() {
            if (!this.bmiIsValid) return null;
            const h = Number(this.bmiHeight) / 100;
            const w = Number(this.bmiWeight);
            if (h <= 0 || w <= 0) return null;
            return Number((w / (h * h)).toFixed(1));
        },

        get bmiValue() {
            return this.bmiNumeric === null ? '--' : this.bmiNumeric.toFixed(1);
        },

        get bmiCategory() {
            const v = this.bmiNumeric;
            if (v === null) {
                return {
                    key: 'invalid',
                    label: 'Periksa Input',
                    color: 'text-slate-600',
                    bg: 'bg-slate-100',
                    border: 'border-slate-200',
                    desc: 'Pastikan tinggi badan, berat badan, dan usia bernilai lebih dari 0 sesuai rentang yang ditentukan.',
                };
            }
            const t = this.bmiThresholds;
            if (v < t.underMax) {
                return {
                    key: 'kurus',
                    label: 'Kurus (Underweight)',
                    color: 'text-amber-700',
                    bg: 'bg-amber-50',
                    border: 'border-amber-200',
                    desc: this.bmiGender === 'l'
                        ? 'IMT di bawah batas normal pria Kemenkes (< 18.0). Fokus pada surplus kalori bergizi seimbang dan latihan beban penguatan otot.'
                        : 'IMT di bawah batas normal wanita Kemenkes (< 17.0). Tingkatkan asupan protein, lemak sehat, dan latihan kekuatan bertahap.',
                };
            }
            if (v <= t.normalMax) {
                return {
                    key: 'normal',
                    label: 'Normal (Ideal)',
                    color: 'text-emerald-700',
                    bg: 'bg-emerald-50',
                    border: 'border-emerald-200',
                    desc: this.bmiGender === 'l'
                        ? 'Bagus! IMT Anda berada dalam rentang ideal pria standar Kemenkes (18.0 – 25.0). Pertahankan dengan rutin berolahraga.'
                        : 'Bagus! IMT Anda berada dalam rentang ideal wanita standar Kemenkes (17.0 – 23.0). Pertahankan pola makan seimbang dan aktivitas fisik rutin.',
                };
            }
            if (v <= t.overMax) {
                return {
                    key: 'gemuk',
                    label: 'Gemuk (Overweight)',
                    color: 'text-orange-700',
                    bg: 'bg-orange-50',
                    border: 'border-orange-200',
                    desc: this.bmiGender === 'l'
                        ? 'IMT berada di kategori kegemukan pria (25.1 – 27.0). Kombinasikan latihan kardio teratur dan defisit kalori ringan.'
                        : 'IMT berada di kategori kegemukan wanita (23.1 – 27.0). Tingkatkan aktivitas aerobik rutin dan atur porsi karbohidrat serta gula.',
                };
            }
            return {
                key: 'obesitas',
                label: 'Obesitas',
                color: 'text-red-700',
                bg: 'bg-red-50',
                border: 'border-red-200',
                desc: 'IMT melebihi 27.0 (Obesitas standar Kemenkes). Disarankan memulai latihan low-impact bertahap dan berkonsultasi dengan tenaga medis atau ahli gizi.',
            };
        },

        get idealWeightBounds() {
            if (this.bmiErrors.height) return null;
            const h = Number(this.bmiHeight) / 100;
            const t = this.bmiThresholds;
            return {
                min: Number((t.normalMin * h * h).toFixed(1)),
                max: Number((t.normalMax * h * h).toFixed(1)),
            };
        },

        get idealWeightRange() {
            if (!this.idealWeightBounds) return '-';
            return `${this.idealWeightBounds.min.toFixed(1)} – ${this.idealWeightBounds.max.toFixed(1)} kg`;
        },

        get brocaIdealWeight() {
            if (this.bmiErrors.height) return '-';
            const tb = Number(this.bmiHeight);
            if (tb < 110) {
                if (!this.idealWeightBounds) return '-';
                return ((this.idealWeightBounds.min + this.idealWeightBounds.max) / 2).toFixed(1) + ' kg';
            }
            if (this.bmiGender === 'l') {
                const val = tb < 160 ? (tb - 100) : (tb - 100) * 0.9;
                return val.toFixed(1) + ' kg';
            }
            const val = tb < 150 ? (tb - 100) : (tb - 100) * 0.85;
            return val.toFixed(1) + ' kg';
        },

        get bmiWeightDelta() {
            if (!this.bmiIsValid || !this.idealWeightBounds) return null;
            const w = Number(this.bmiWeight);
            const { min, max } = this.idealWeightBounds;
            if (w < min) {
                return { tone: 'amber', text: `Perlu naik sekitar +${(min - w).toFixed(1)} kg untuk masuk batas ideal` };
            }
            if (w > max) {
                return { tone: 'orange', text: `Perlu turun sekitar -${(w - max).toFixed(1)} kg untuk masuk batas ideal` };
            }
            return { tone: 'emerald', text: 'Berat badan sudah berada di rentang ideal Kemenkes' };
        },

        get bmiWaterRecommendation() {
            const age = Number(this.bmiAge) || 24;
            if (this.bmiGender === 'l') {
                if (age < 13) return '~ 1.85 L / hari (± 8 gelas)';
                if (age <= 15) return '~ 2.1 L / hari (± 9 gelas)';
                if (age <= 18) return '~ 2.3 L / hari (± 9–10 gelas)';
                if (age <= 64) return '~ 2.5 L / hari (± 10 gelas)';
                return '~ 1.8 L / hari (± 7–8 gelas)';
            }
            if (age < 13) return '~ 1.85 L / hari (± 8 gelas)';
            if (age <= 18) return '~ 2.1 L / hari (± 8–9 gelas)';
            if (age <= 64) return '~ 2.35 L / hari (± 9–10 gelas)';
            return '~ 1.55 L / hari (± 6–7 gelas)';
        },

        get bmiGaugeSegments() {
            const t = this.bmiThresholds;
            return {
                kurus: ((t.underMax - 12) / 24) * 100,
                normal: ((t.normalMax - t.underMax) / 24) * 100,
                gemuk: ((t.overMax - t.normalMax) / 24) * 100,
                obesitas: ((36 - t.overMax) / 24) * 100,
            };
        },

        get bmiGaugePercent() {
            if (this.bmiNumeric === null) return 0;
            return Math.max(2, Math.min(98, ((this.bmiNumeric - 12) / 24) * 100));
        },

        saveBmiHistory() {
            if (!this.bmiIsValid || this.bmiNumeric === null) return;
            const entry = {
                id: Date.now(),
                date: new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }),
                gender: this.bmiGender,
                genderLabel: this.bmiThresholds.genderLabel,
                height: Number(this.bmiHeight),
                weight: Number(this.bmiWeight),
                age: Number(this.bmiAge),
                bmi: this.bmiNumeric.toFixed(1),
                category: this.bmiCategory.label,
                categoryKey: this.bmiCategory.key,
            };
            this.bmiHistory = [entry, ...this.bmiHistory].slice(0, 5);
            try { localStorage.setItem('bugarin_bmi_history_v1', JSON.stringify(this.bmiHistory)); } catch (e) {}
            this.bmiSavedFlash = true;
            setTimeout(() => { this.bmiSavedFlash = false; }, 2000);
        },

        loadBmiHistory(item) {
            if (!item) return;
            this.bmiGender = item.gender || 'l';
            this.bmiHeight = Number(item.height) || 170;
            this.bmiWeight = Number(item.weight) || 65;
            this.bmiAge = Number(item.age) || 24;
        },

        removeBmiHistory(id) {
            this.bmiHistory = this.bmiHistory.filter(item => item.id !== id);
            try { localStorage.setItem('bugarin_bmi_history_v1', JSON.stringify(this.bmiHistory)); } catch (e) {}
        },

        clearBmiHistory() {
            this.bmiHistory = [];
            try { localStorage.removeItem('bugarin_bmi_history_v1'); } catch (e) {}
        },

        streakMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        streakSelectedDate: new Date().toLocaleDateString('sv-SE'),
        streakActivityDates: @js($streakActivityDates),
        streakActivities: @js($verifiedActivities),
        streakCalendarCells() {
            const first = new Date(this.streakMonth.getFullYear(), this.streakMonth.getMonth(), 1);
            const start = new Date(first);
            start.setDate(first.getDate() - ((first.getDay() + 6) % 7));
            return Array.from({ length: 42 }, (_, index) => {
                const date = new Date(start);
                date.setDate(start.getDate() + index);
                return date;
            });
        },
        streakActivityOn(date) {
            return this.streakActivityDates.includes(this.agendaDateKey(date));
        },
        streakActivitiesOnSelectedDate() {
            return this.streakActivities.filter(activity => activity.date === this.streakSelectedDate);
        },
        streakShiftMonth(amount) {
            this.streakMonth = new Date(this.streakMonth.getFullYear(), this.streakMonth.getMonth() + amount, 1);
        },

        // --- QR Check-in Modal state ---
        qrOpen: false,
        qrScheduleId: null,
        qrScheduleTitle: '',
        qrToken: null,
        qrExpiresIn: 0,
        qrRotateIn: 0,
        qrCheckedIn: false,
        qrCheckedAt: '',
        qrPointsAwarded: 0,
        qrCurrentStreak: 0,
        qrLoading: false,
        qrError: '',
        _qrTimerRef: null,
        _qrPollRef: null,
        _qrRotateRef: null,

        qrOpenModal(scheduleId, scheduleTitle) {
            this.qrScheduleId = scheduleId;
            this.qrScheduleTitle = scheduleTitle;
            this.qrToken = null;
            this.qrExpiresIn = 0;
            this.qrRotateIn = 0;
            this.qrCheckedIn = false;
            this.qrCheckedAt = '';
            this.qrPointsAwarded = 0;
            this.qrCurrentStreak = 0;
            this.qrLoading = true;
            this.qrError = '';
            this.qrOpen = true;
            this._qrFetchToken();
        },

        qrCloseModal() {
            this.qrOpen = false;
            this._qrStopAll();
        },

        _qrStopAll() {
            if (this._qrTimerRef) { clearInterval(this._qrTimerRef); this._qrTimerRef = null; }
            if (this._qrPollRef) { clearInterval(this._qrPollRef); this._qrPollRef = null; }
            if (this._qrRotateRef) { clearTimeout(this._qrRotateRef); this._qrRotateRef = null; }
        },

        async _qrFetchToken() {
            this.qrLoading = true;
            this.qrError = '';
            const csrfMeta = document.querySelector('meta[name=csrf-token]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            try {
                const resp = await fetch(`/user/schedules/${this.qrScheduleId}/checkin-token`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' }
                });
                const data = await resp.json();
                if (!resp.ok) {
                    this.qrError = data.message || 'Gagal memuat token QR.';
                    this.qrLoading = false;
                    return;
                }
                this.qrToken = data.token;
                this.qrExpiresIn = data.expires_in;
                this.qrRotateIn = data.rotate_in;
                this.qrLoading = false;
                this.$nextTick(function() { this._qrRender(); }.bind(this));
                this._qrStartCountdown();
                this._qrScheduleRotate();
                if (!this._qrPollRef) this._qrStartPolling();
            } catch (e) {
                this.qrError = 'Koneksi gagal. Periksa internet Anda.';
                this.qrLoading = false;
            }
        },

        _qrRender() {
            const el = document.getElementById('qr-canvas-container');
            if (!el || !this.qrToken) return;
            if (typeof QRCode === 'undefined') {
                setTimeout(function() { this._qrRender(); }.bind(this), 250);
                return;
            }
            el.innerHTML = '';
            new QRCode(el, {
                text: this.qrToken,
                width: 200,
                height: 200,
                colorDark: '#1d4ed8',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        },

        _qrStartCountdown() {
            if (this._qrTimerRef) clearInterval(this._qrTimerRef);
            this._qrTimerRef = setInterval(() => {
                if (this.qrExpiresIn > 0) this.qrExpiresIn--;
                if (this.qrRotateIn > 0) this.qrRotateIn--;
            }, 1000);
        },

        _qrScheduleRotate() {
            if (this._qrRotateRef) clearTimeout(this._qrRotateRef);
            const delay = (this.qrRotateIn > 0 ? this.qrRotateIn : 45) * 1000;
            this._qrRotateRef = setTimeout(() => {
                if (this.qrOpen && !this.qrCheckedIn) this._qrFetchToken();
            }, delay);
        },

        _qrStartPolling() {
            this._qrPollRef = setInterval(async () => {
                if (!this.qrOpen || this.qrCheckedIn) { clearInterval(this._qrPollRef); this._qrPollRef = null; return; }
                try {
                    const resp = await fetch(`/user/schedules/${this.qrScheduleId}/checkin-status`, { headers: { 'Accept': 'application/json' } });
                    const data = await resp.json();
                    if (data.checked_in) {
                        this.qrCheckedIn = true;
                        this.qrCheckedAt = data.checked_in_at || '';
                        this.qrPointsAwarded = data.points_awarded || 0;
                        this.qrCurrentStreak = data.current_streak || 0;
                        this._qrStopAll();
                    }
                } catch (e) {}
            }, 3500);
        }
    }" class="space-y-8 w-full max-w-7xl">

        {{-- Flash messages --}}
        @if(session('error'))
            <div role="status" class="flex items-start gap-3 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm font-semibold text-rose-800 shadow-xs">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs font-bold">!</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div role="status" class="flex items-start gap-3 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-semibold text-emerald-800 shadow-xs">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div role="status" class="flex items-start gap-3 rounded-2xl bg-blue-50 border border-blue-200 p-4 text-sm font-semibold text-blue-800 shadow-xs">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold">i</span>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if(session('status') || session('streak_error') || session('participation_error'))
            <div role="status" class="rounded-xl {{ session('streak_error') || session('participation_error') ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-700' }} p-4 text-sm font-semibold">
                {{ session('status') ?? session('streak_error') ?? session('participation_error') }}
            </div>
        @endif

        @if(Auth::user()->isJoinBlocked())
            <div role="alert" class="flex items-start gap-3 rounded-2xl bg-amber-50 border border-amber-200 p-4 sm:p-5 text-sm font-semibold text-amber-900 shadow-xs">
                <span class="flex-shrink-0 text-2xl">⚠️</span>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-amber-950">Pendaftaran Jadwal Sedang Dibatasi</h4>
                    <p class="mt-0.5 text-xs text-amber-800">
                        Akun Anda terkena sanksi pembatasan pendaftaran jadwal hingga <strong>{{ Auth::user()->join_blocked_until->timezone(config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d F Y, H:i') }} WIB</strong> karena akumulasi ketidakhadiran (no-show).
                    </p>
                </div>
            </div>
        @endif

        <section aria-labelledby="user-announcements-heading" class="glass-card rounded-2xl border border-cyan-100 bg-gradient-to-r from-white via-blue-50/60 to-cyan-50/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-cyan-700">Informasi untukmu</span>
                    <h2 id="user-announcements-heading" class="mt-1 text-xl font-extrabold text-blue-800">Pengumuman &amp; Pengingat</h2>
                    <p class="mt-1 text-xs text-slate-600">Pengingat event dan jadwal dengan pendaftaran aktif akan muncul menjelang waktu pelaksanaan.</p>
                </div>
                @if($announcements->isNotEmpty())
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-cyan-700 shadow-xs">{{ $announcements->whereNull('read_at')->count() }} belum dibaca</span>
                @endif
            </div>

            @if($todayJoinedSchedule)
                @php
                    $tjsItemPayload = [
                        'id' => 'schedule-' . $todayJoinedSchedule->id,
                        'raw_id' => $todayJoinedSchedule->id,
                        'kind' => 'schedule',
                        'type' => 'latihan_rutin',
                        'title' => $todayJoinedSchedule->title,
                        'level' => $todayJoinedSchedule->level,
                        'level_label' => $todayJoinedSchedule->levelLabel(),
                        'level_badge_class' => $todayJoinedSchedule->levelBadgeClass(),
                        'description' => $todayJoinedSchedule->description,
                        'community' => $todayJoinedSchedule->community?->name ?? 'Komunitas',
                        'sport' => $todayJoinedSchedule->community?->sport_category ?? 'olahraga',
                        'location' => $todayJoinedSchedule->location,
                        'maps_url' => $todayJoinedSchedule->maps_url,
                        'date' => $todayJoinedSchedule->date ? $todayJoinedSchedule->date->format('Y-m-d') : null,
                        'date_formatted' => $todayJoinedSchedule->date ? $todayJoinedSchedule->date->locale('id')->isoFormat('dddd, D MMMM Y') : $todayJoinedSchedule->dayLabel(),
                        'start' => substr($todayJoinedSchedule->start_time, 0, 5),
                        'end' => substr($todayJoinedSchedule->end_time, 0, 5),
                        'capacity' => $todayJoinedSchedule->capacity,
                        'filled' => $todayJoinedSchedule->activeParticipantsCount(),
                        'remaining' => $todayJoinedSchedule->remainingSlots(),
                        'status' => $todayJoinedSchedule->status,
                        'is_finished' => $todayJoinedSchedule->isFinished(),
                        'is_cancelled' => $todayJoinedSchedule->isCancelled(),
                        'streak_enabled' => (bool) $todayJoinedSchedule->streak_enabled,
                        'is_today' => true,
                    ];
                @endphp
                <article class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50/90 via-white to-cyan-50/80 p-4 sm:p-5 shadow-xs">
                    <div class="flex min-w-0 flex-1 items-start gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 text-white font-bold text-lg shadow-xs" aria-hidden="true">⚡</span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-blue-600 text-white px-2 py-0.5 text-[10px] font-bold">Hari Ini</span>
                                <span class="rounded-full bg-emerald-100 text-emerald-800 px-2 py-0.5 text-[10px] font-bold">Terdaftar</span>
                                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 truncate">{{ $todayJoinedSchedule->title }}</h3>
                            </div>
                            <p class="mt-1 text-xs text-slate-600 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span>⏰ {{ substr($todayJoinedSchedule->start_time, 0, 5) }} - {{ substr($todayJoinedSchedule->end_time, 0, 5) }} WIB</span>
                                <span>📍 {{ $todayJoinedSchedule->location }}</span>
                                <span>👥 {{ $todayJoinedSchedule->community?->name }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button"
                            @click="openScheduleDetail(@js($tjsItemPayload))"
                            class="rounded-xl bg-blue-600 hover:bg-blue-500 text-white px-4 py-2.5 text-xs font-bold transition shadow-xs flex items-center gap-1.5 shrink-0">
                        <span>Lihat Detail Sesi &rarr;</span>
                    </button>
                </article>
            @endif

            @if($announcements->isEmpty() && !$todayJoinedSchedule)
                <p class="mt-4 rounded-xl border border-blue-100 bg-white/80 p-4 text-sm text-slate-600">Belum ada pengingat kegiatan. Setelah pendaftaran event atau jadwal aktif, pengingat akan tampil sekitar 24 jam sebelum kegiatan.</p>
            @elseif($announcements->isNotEmpty())
                <div class="mt-4 space-y-3">
                    @foreach($announcements as $announcement)
                        <article class="flex flex-wrap items-start justify-between gap-3 rounded-xl border {{ $announcement->read_at ? 'border-slate-100 bg-white/70' : 'border-cyan-100 bg-white' }} p-4">
                            <div class="flex min-w-0 flex-1 items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $announcement->type === 'event_reminder' ? 'bg-cyan-100 text-cyan-700' : 'bg-blue-100 text-blue-700' }}" aria-hidden="true">{{ $announcement->type === 'event_reminder' ? '📅' : '🏃' }}</span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-900">{{ $announcement->title }}</h3>
                                        @if(!$announcement->read_at)<span class="rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-bold text-cyan-800">Baru</span>@endif
                                    </div>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $announcement->body }}</p>
                                    <time class="mt-2 block text-[10px] text-slate-400" datetime="{{ $announcement->created_at?->toIso8601String() }}">Dibuat {{ $announcement->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</time>
                                </div>
                            </div>
                            @if(!$announcement->read_at)
                                <form method="POST" action="{{ route('user.announcements.read', $announcement) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-blue-100 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Tandai dibaca</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============================================================
             5. MODUL: KALENDER JADWAL & AGENDA OLAHRAGA
             ============================================================ --}}
        <section x-show="activeSection === 'agenda'" x-cloak class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-blue-200/80 bg-gradient-to-r from-white via-blue-50/70 to-cyan-50/40 p-6 sm:p-8 shadow-xs">
                <div class="relative z-10 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-blue-600"><span aria-hidden="true">▦</span> Agenda Olahraga Terjadwal</span>
                        <h3 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Jadwal &amp; Agenda Olahraga</h3>
                        <p class="mt-2 max-w-2xl text-sm sm:text-base text-slate-600">Temukan sesi latihan rutin, fun match, dan turnamen dari komunitas yang kamu ikuti.</p>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="sr-only" for="agenda-community-filter">Filter komunitas</label>
                        <select id="agenda-community-filter" x-model="agendaCommunityFilter" class="glass-input rounded-xl px-4 py-3 text-sm text-slate-700">
                            <option value="all">Semua Komunitas</option>
                            <template x-for="community in agendaCommunities" :key="community"><option :value="community" x-text="community"></option></template>
                        </select>
                        <label class="sr-only" for="agenda-type-filter">Filter jenis kegiatan</label>
                        <select id="agenda-type-filter" x-model="agendaTypeFilter" class="glass-input rounded-xl px-4 py-3 text-sm text-slate-700">
                            <option value="all">Semua Jenis Kegiatan</option>
                            <option value="latihan_rutin">Latihan Rutin</option>
                            <option value="fun_match">Fun Match</option>
                            <option value="tournament">Turnamen</option>
                            <option value="gathering">Gathering</option>
                            <option value="workshop">Workshop</option>
                            <option value="other">Lainnya</option>
                        </select>

                        {{-- Filter: Semua / Paling Ramai / Diikuti Saya --}}
                        <div class="flex rounded-xl border border-blue-100 bg-white/90 p-1 sm:col-span-2 shadow-2xs gap-1">
                            <button type="button" @click="agendaScheduleFilter = 'all'"
                                :class="agendaScheduleFilter === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition-all">
                                🗓 Semua
                            </button>
                            <button type="button" @click="agendaScheduleFilter = 'popular'"
                                :class="agendaScheduleFilter === 'popular' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition-all">
                                🔥 Terpopuler
                            </button>
                            <button type="button" @click="agendaScheduleFilter = 'mine'"
                                :class="agendaScheduleFilter === 'mine' ? 'bg-cyan-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition-all">
                                ✓ Diikuti Saya
                            </button>
                        </div>

                        <div class="flex rounded-xl border border-blue-100 bg-white/90 p-1 sm:col-span-2 shadow-2xs">
                            <button type="button" @click="agendaView = 'calendar'" :class="agendaView === 'calendar' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold transition-all">▦ Kalender Bulan</button>
                            <button type="button" @click="agendaView = 'list'" :class="agendaView === 'list' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold transition-all">☷ Daftar List</button>
                        </div>

                        {{-- Info badge ketika filter aktif --}}
                        <div x-show="agendaScheduleFilter !== 'all'" class="sm:col-span-2 flex items-center gap-2 rounded-xl bg-blue-50 border border-blue-100 px-3 py-2 text-xs text-blue-700 font-semibold">
                            <span x-show="agendaScheduleFilter === 'mine'">✓ Menampilkan jadwal yang sudah kamu ikuti</span>
                            <span x-show="agendaScheduleFilter === 'popular'">🔥 Menampilkan jadwal dengan kepadatan peserta ≥50%</span>
                            <button type="button" @click="agendaScheduleFilter = 'all'" class="ml-auto text-[11px] underline text-blue-500">Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.9fr)_minmax(320px,0.95fr)]">
                <div x-show="agendaView === 'calendar'" class="glass-card rounded-2xl p-4 sm:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <h4 class="text-xl sm:text-2xl font-extrabold text-slate-900" x-text="agendaMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })"></h4>
                            <div class="flex items-center gap-1 rounded-xl bg-blue-50/80 p-1 border border-blue-100">
                                <button type="button" @click="agendaShiftMonth(-1)" class="rounded-lg px-2.5 py-1.5 text-blue-700 hover:bg-white transition-colors" aria-label="Bulan sebelumnya">‹</button>
                                <button type="button" @click="agendaMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1); agendaSelectDate(new Date())" class="rounded-lg bg-white px-2.5 py-1.5 text-xs font-semibold text-blue-700 shadow-2xs">Hari Ini</button>
                                <button type="button" @click="agendaShiftMonth(1)" class="rounded-lg px-2.5 py-1.5 text-blue-700 hover:bg-white transition-colors" aria-label="Bulan berikutnya">›</button>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 text-[11px] font-semibold text-slate-600">
                            <span class="rounded-full bg-blue-50 border border-blue-100 px-2.5 py-1 text-blue-700">● Latihan Rutin</span>
                            <span class="rounded-full bg-cyan-50 border border-cyan-100 px-2.5 py-1 text-cyan-700">● Fun Match</span>
                            <span class="rounded-full bg-amber-50 border border-amber-100 px-2.5 py-1 text-amber-700">● Turnamen</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day"><div class="py-2 text-center text-[10px] font-bold uppercase tracking-wide text-slate-400" x-text="day"></div></template>
                        <template x-for="date in agendaCalendarCells()" :key="agendaDateKey(date)">
                            <button type="button" @click="agendaSelectDate(date)" :class="[
                                date.getMonth() === agendaMonth.getMonth() ? 'bg-blue-50/40 text-slate-700' : 'bg-slate-50/50 text-slate-300',
                                agendaSelectedDate === agendaDateKey(date) ? 'ring-2 ring-blue-500 bg-white shadow-xs' : 'hover:bg-blue-100/50',
                                agendaDateKey(date) === agendaDateKey(new Date()) ? 'font-extrabold border border-blue-300' : 'border border-slate-100'
                            ].join(' ')" class="min-h-20 sm:min-h-24 rounded-xl p-1.5 sm:p-2 text-left transition-colors">
                                <span class="text-xs" x-text="date.getDate()"></span>
                                <div class="mt-1 space-y-1">
                                    <template x-for="item in agendaForDate(date).slice(0, 2)" :key="item.id">
                                        <button type="button"
                                                @click.stop="agendaSelectDate(date); agendaSelectedId = item.id; openScheduleDetail(item)"
                                                :class="item.type === 'latihan_rutin' ? 'bg-blue-100/90 text-blue-800 hover:bg-blue-200' : (item.type === 'fun_match' ? 'bg-cyan-100/90 text-cyan-800 hover:bg-cyan-200' : 'bg-amber-100/90 text-amber-800 hover:bg-amber-200')"
                                                class="block w-full text-left truncate rounded-md px-1 py-0.5 text-[9px] font-semibold cursor-pointer transition"
                                                x-text="`${item.start} ${item.title}`">
                                        </button>
                                    </template>
                                    <span x-show="agendaForDate(date).length > 2" class="block text-[9px] text-slate-500" x-text="`+${agendaForDate(date).length - 2} agenda`"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="agendaView === 'list'" class="glass-card rounded-2xl p-5 sm:p-6">
                    <h4 class="text-xl font-extrabold text-slate-900">Agenda Mendatang</h4>
                    <p class="mb-4 mt-1 text-xs text-slate-500">Jadwal dalam 60 hari ke depan</p>
                    <div x-show="agendaUpcoming.length === 0" class="rounded-xl bg-blue-50/50 border border-blue-100 p-6 text-center text-sm text-slate-500">Belum ada agenda olahraga yang sesuai filter.</div>
                    <div class="max-h-[680px] space-y-3 overflow-y-auto pr-1">
                        <template x-for="item in agendaUpcoming" :key="`${item.id}-${item.occurrenceDate}`">
                            <button type="button" @click="agendaSelectedDate = item.occurrenceDate; agendaSelectedId = item.id; agendaMonth = new Date(`${item.occurrenceDate}T00:00:00`); openScheduleDetail(item)" class="w-full rounded-xl border border-slate-100 bg-white/90 p-4 text-left transition hover:border-blue-300 hover:shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-blue-600" x-text="new Date(`${item.occurrenceDate}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })"></span>
                                <span class="mt-1 block font-bold text-slate-900" x-text="item.title"></span>
                                <span class="mt-1 block text-xs text-slate-500" x-text="`${item.start} WIB · ${item.community}`"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <aside class="space-y-5">
                    <div class="glass-card rounded-2xl p-5 sm:p-6">
                        <template x-if="agendaSelectedItem">
                            <div>
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span :class="agendaSelectedItem.type === 'latihan_rutin' ? 'bg-blue-100 text-blue-700' : 'bg-cyan-100 text-cyan-700'" class="rounded-full px-3 py-1 text-[10px] font-bold" x-text="agendaSelectedItem.type === 'latihan_rutin' ? 'Latihan Rutin' : agendaSelectedItem.type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"></span>
                                        <span x-show="agendaSelectedItem.level_label" :class="agendaSelectedItem.level_badge_class || 'bg-slate-100 text-slate-700 border-slate-200'" class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border" x-text="agendaSelectedItem.level_label"></span>
                                        <span x-show="agendaSelectedItem.is_recurring" class="rounded-full bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-[10px] font-bold text-indigo-700">🔗 Seri</span>
                                        <span x-show="agendaSelectedItem.streak_enabled" class="rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-[10px] font-bold text-amber-700">🔥 Streak Aktif</span>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-600" x-text="agendaSelectedItem.sport"></span>
                                </div>
                                <h4 class="text-2xl font-extrabold leading-tight text-slate-900" x-text="agendaSelectedItem.title"></h4>
                                <p class="mt-2 text-sm text-slate-500" x-text="agendaSelectedItem.description || `Kegiatan olahraga bersama ${agendaSelectedItem.community}.`"></p>
                                <div class="mt-5 space-y-4 rounded-xl bg-blue-50/70 border border-blue-100 p-4">
                                    <div><span class="block text-[10px] font-bold uppercase text-slate-400">Waktu &amp; Tanggal</span><strong class="mt-1 block text-sm text-slate-800" x-text="agendaSelectedItem.date ? new Date(`${agendaSelectedItem.date}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : `${agendaSelectedItem.start} WIB`"></strong><span class="text-xs text-slate-500" x-text="`${agendaSelectedItem.start}${agendaSelectedItem.end ? ` – ${agendaSelectedItem.end}` : ''} WIB`"></span></div>
                                    <div><span class="block text-[10px] font-bold uppercase text-slate-400">Titik Kumpul</span><strong class="mt-1 block text-sm text-slate-800" x-text="agendaSelectedItem.location || 'Lokasi belum ditentukan'"></strong><span class="text-xs text-slate-500" x-text="agendaSelectedItem.community"></span></div>
                                    <div><span class="block text-[10px] font-bold uppercase text-slate-400">Kapasitas Slot</span><strong class="mt-1 block text-sm text-slate-800" x-text="agendaSelectedItem.capacity ? `${agendaSelectedItem.filled || 0}/${agendaSelectedItem.capacity} Peserta` : `${agendaSelectedItem.filled || 0} Peserta (Tanpa batas slot)`"></strong></div>
                                </div>
                                <a x-show="agendaSelectedItem.maps_url" :href="agendaSelectedItem.maps_url" target="_blank" rel="noopener noreferrer" class="mt-5 block w-full rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 px-4 py-3 text-center text-sm font-bold text-white shadow-md shadow-blue-500/20 transition-all">Buka Peta Lokasi <span aria-hidden="true">→</span></a>

                                {{-- Status join / tombol join berdasarkan item yang dipilih --}}
                                <div class="mt-3 space-y-2" x-show="agendaSelectedItem">
                                    <div x-show="isJoinedItem(agendaSelectedItem)"
                                         class="flex items-center justify-between gap-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-800">
                                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]">✓</span>
                                            <span x-text="agendaSelectedItem.is_finished ? 'Terdaftar (Selesai)' : 'Terdaftar'"></span>
                                        </div>
                                        <template x-if="agendaSelectedItem && agendaSelectedItem.kind === 'schedule' && !agendaSelectedItem.is_finished">
                                            <form method="POST" action="{{ route('user.participation.cancel') }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan keikutsertaan dalam jadwal ini?')">
                                                @csrf
                                                <input type="hidden" name="type" value="schedule">
                                                <input type="hidden" name="id" :value="agendaSelectedItem.raw_id || agendaSelectedItem.id.replace('schedule-','')">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg border border-rose-200 bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold transition">
                                                    Batalkan
                                                </button>
                                            </form>
                                        </template>
                                    </div>
                                    <div x-show="!isJoinedItem(agendaSelectedItem)" class="space-y-2">
                                        <template x-if="agendaSelectedItem && agendaSelectedItem.kind === 'schedule'">
                                            <form method="POST" :action="`/user/schedules/${agendaSelectedItem.raw_id || agendaSelectedItem.id.replace('schedule-','')}/join`">
                                                @csrf
                                                <button type="submit"
                                                        :disabled="(agendaSelectedItem.capacity && agendaSelectedItem.filled >= agendaSelectedItem.capacity) || agendaSelectedItem.is_finished"
                                                        :class="((agendaSelectedItem.capacity && agendaSelectedItem.filled >= agendaSelectedItem.capacity) || agendaSelectedItem.is_finished) ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-gradient-to-r from-blue-600 to-blue-500 hover:brightness-105 text-white'"
                                                        class="w-full rounded-xl px-4 py-3 text-center text-sm font-bold shadow-xs transition-all"
                                                        x-text="agendaSelectedItem.is_finished ? 'Sesi Telah Selesai' : ((agendaSelectedItem.capacity && agendaSelectedItem.filled >= agendaSelectedItem.capacity) ? 'Kuota Peserta Penuh' : 'Ikuti Sesi Latihan Ini →')">
                                                </button>
                                            </form>
                                        </template>
                                        <template x-if="agendaSelectedItem && agendaSelectedItem.kind === 'event'">
                                            <form method="POST" :action="`/user/events/${agendaSelectedItem.id.replace('event-','')}/join`">
                                                @csrf
                                                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:brightness-105 px-4 py-3 text-center text-sm font-bold text-white shadow-xs transition-all">
                                                    Daftar Event Ini →
                                                </button>
                                            </form>
                                        </template>
                                    </div>

                                    <button type="button" @click="openScheduleDetail(agendaSelectedItem)" class="w-full py-2 px-3 rounded-xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-blue-700 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                        <span>Buka Modal Detail</span>
                                        <span>&rarr;</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div x-show="!agendaSelectedItem" class="py-8 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-xl text-blue-600 border border-blue-100">▦</div>
                            <h4 class="font-bold text-slate-800">Pilih tanggal agenda</h4>
                            <p class="mt-1 text-xs text-slate-500">Pilih tanggal pada kalender untuk melihat detail kegiatan.</p>
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl p-5">
                        <h4 class="font-bold text-slate-900">Agenda olahraga</h4>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs"><span class="rounded-full bg-blue-50 border border-blue-100 px-3 py-1.5 text-blue-700 font-semibold">{{ $schedules->count() }} latihan rutin</span><span class="rounded-full bg-cyan-50 border border-cyan-100 px-3 py-1.5 text-cyan-700 font-semibold">{{ $events->count() }} event</span></div>
                    </div>
                </aside>
            </div>

            {{-- MODAL DETAIL JADWAL / AGENDA --}}
            <div x-show="scheduleDetailModalOpen"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                 @keydown.escape.window="scheduleDetailModalOpen = false">
                <template x-if="modalScheduleItem">
                    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 relative space-y-5"
                         @click.away="scheduleDetailModalOpen = false">
                        
                        {{-- Close button --}}
                        <button type="button" @click="scheduleDetailModalOpen = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        {{-- Badges --}}
                        <div class="flex items-center gap-2 flex-wrap pr-8">
                            <span :class="modalScheduleItem.type === 'latihan_rutin' ? 'bg-blue-100 text-blue-700' : 'bg-cyan-100 text-cyan-700'" class="rounded-full px-2.5 py-0.5 text-[10px] font-bold" x-text="modalScheduleItem.type === 'latihan_rutin' ? 'Latihan Rutin' : modalScheduleItem.type"></span>
                            <span x-show="modalScheduleItem.level_label" :class="modalScheduleItem.level_badge_class || 'bg-slate-100 text-slate-700 border-slate-200'" class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border" x-text="modalScheduleItem.level_label"></span>
                            <span x-show="modalScheduleItem.is_recurring" class="rounded-full bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-[10px] font-bold text-indigo-700">🔗 Seri</span>
                            <span x-show="modalScheduleItem.streak_enabled" class="rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-[10px] font-bold text-amber-700">🔥 Streak Aktif</span>
                            <span x-show="modalScheduleItem.is_finished" class="rounded-full bg-slate-100 border border-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">✓ Selesai</span>
                        </div>

                        {{-- Judul & Deskripsi --}}
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-900 leading-snug" x-text="modalScheduleItem.title"></h3>
                            <p class="text-xs text-slate-500 mt-1" x-text="modalScheduleItem.description || `Kegiatan olahraga bersama ${modalScheduleItem.community}.`"></p>
                        </div>

                        {{-- Detail Box --}}
                        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 space-y-3 text-xs">
                            <div class="flex items-start gap-2.5">
                                <span class="text-base shrink-0">📅</span>
                                <div>
                                    <span class="block font-bold text-slate-900" x-text="modalScheduleItem.date_formatted || (modalScheduleItem.date ? new Date(`${modalScheduleItem.date}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : `${modalScheduleItem.start} WIB`)"></span>
                                    <span class="text-slate-500 font-medium" x-text="`${modalScheduleItem.start}${modalScheduleItem.end ? ` – ${modalScheduleItem.end}` : ''} WIB`"></span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5">
                                <span class="text-base shrink-0">📍</span>
                                <div class="space-y-1">
                                    <span class="block font-bold text-slate-900" x-text="modalScheduleItem.location || 'Lokasi belum ditentukan'"></span>
                                    <span class="text-slate-500 block" x-text="modalScheduleItem.community"></span>
                                    <template x-if="modalScheduleItem.maps_url">
                                        <a :href="modalScheduleItem.maps_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-bold">
                                            <span>Buka di Google Maps</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5">
                                <span class="text-base shrink-0">👥</span>
                                <div>
                                    <span class="block font-bold text-slate-900">Kapasitas Slot</span>
                                    <span class="text-slate-600" x-text="modalScheduleItem.capacity ? `${modalScheduleItem.filled || 0}/${modalScheduleItem.capacity} peserta terdaftar` : `${modalScheduleItem.filled || 0} peserta terdaftar · Tanpa batas slot`"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Footer --}}
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                            <button type="button" @click="scheduleDetailModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                                Tutup
                            </button>

                            <div>
                                {{-- Jika sudah terdaftar --}}
                                <template x-if="isJoinedItem(modalScheduleItem)">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                                            ✓ Terdaftar
                                        </span>
                                        <template x-if="modalScheduleItem && modalScheduleItem.kind === 'schedule' && modalScheduleItem.streak_enabled && (modalScheduleItem.is_today || modalScheduleItem.date === (new Date().toLocaleDateString('sv-SE')) || modalScheduleItem.date === agendaDateKey(new Date())) && !modalScheduleItem.is_finished">
                                            <button type="button"
                                                    @click="scheduleDetailModalOpen = false; qrOpenModal(parseInt(modalScheduleItem.raw_id || modalScheduleItem.id.replace('schedule-','')), modalScheduleItem.title)"
                                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:brightness-105 text-white text-xs font-black shadow-sm transition flex items-center gap-1.5">
                                                📷 Tampilkan QR Check-in
                                            </button>
                                        </template>
                                        <template x-if="modalScheduleItem.kind === 'schedule' && !modalScheduleItem.is_finished">
                                            <form method="POST" action="{{ route('user.participation.cancel') }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan keikutsertaan dalam jadwal ini?')">
                                                @csrf
                                                <input type="hidden" name="type" value="schedule">
                                                <input type="hidden" name="id" :value="modalScheduleItem.raw_id || modalScheduleItem.id.replace('schedule-','')">
                                                <button type="submit" class="px-4 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition">
                                                    Batalkan
                                                </button>
                                            </form>
                                        </template>
                                    </div>
                                </template>

                                {{-- Jika belum terdaftar --}}
                                <template x-if="!isJoinedItem(modalScheduleItem)">
                                    <div class="space-y-2">
                                        <template x-if="modalScheduleItem.kind === 'schedule'">
                                            <form method="POST" :action="`/user/schedules/${modalScheduleItem.raw_id || modalScheduleItem.id.replace('schedule-','')}/join`">
                                                @csrf
                                                <button type="submit"
                                                        :disabled="(modalScheduleItem.capacity && modalScheduleItem.filled >= modalScheduleItem.capacity) || modalScheduleItem.is_finished"
                                                        :class="((modalScheduleItem.capacity && modalScheduleItem.filled >= modalScheduleItem.capacity) || modalScheduleItem.is_finished) ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'btn-primary'"
                                                        class="px-5 py-2.5 rounded-xl font-bold text-xs transition shadow-xs"
                                                        x-text="modalScheduleItem.is_finished ? 'Sesi Telah Selesai' : ((modalScheduleItem.capacity && modalScheduleItem.filled >= modalScheduleItem.capacity) ? 'Kuota Penuh' : 'Ikuti Sesi Ini →')">
                                                </button>
                                            </form>
                                        </template>
                                        <template x-if="modalScheduleItem.kind === 'event'">
                                            <form method="POST" :action="`/user/events/${modalScheduleItem.id.replace('event-','')}/join`">
                                                @csrf
                                                <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl font-bold text-xs transition shadow-xs">
                                                    Daftar Event Ini →
                                                </button>
                                            </form>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </template>
            </div>
        </section>

        {{-- ============================================================
             6. LEADERBOARD USER OVERALL
             ============================================================ --}}
        <section x-show="activeSection === 'leaderboard'" x-cloak class="space-y-6">
            {{-- Section banner --}}
            <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-r from-white via-blue-50/40 to-sky-50/30 p-6 sm:p-8 shadow-xs">
                <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-gradient-to-br from-blue-200/20 to-transparent blur-3xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-700">
                            <span class="active-dot"></span>Papan Peringkat Bugar.in
                        </span>
                        <h3 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Leaderboard User Overall</h3>
                        <p class="mt-2 max-w-2xl text-sm text-slate-500">Peringkat pengguna berdasarkan poin yang tercatat, sesi olahraga yang dihadiri, dan streak aktivitas.</p>
                    </div>
                    <div class="rounded-xl border border-blue-100 bg-white/90 px-4 py-3 text-xs font-semibold text-slate-600">
                        <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Keseluruhan pengguna aktif</span>
                    </div>
                </div>
            </div>

            {{-- Stats row — editorial, variasi warna --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="activity-tile">
                    <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengguna Aktif</p><div class="tile-icon tile-icon--blue"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div></div>
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($leaderboardStats['users'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400">Akun pengguna terdaftar</p>
                </div>
                <div class="activity-tile activity-tile--hero">
                    <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-wider text-white/70">Total Poin</p><div class="tile-icon tile-icon--white"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></div></div>
                    <p class="text-2xl font-extrabold text-white tabular-nums">{{ number_format($leaderboardStats['points'], 0, ',', '.') }}</p>
                    <p class="text-xs text-white/60">Poin yang tercatat</p>
                </div>
                <div class="activity-tile">
                    <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sesi Dihadiri</p><div class="tile-icon tile-icon--cyan"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div></div>
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($leaderboardStats['sessions'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400">Kehadiran kegiatan komunitas</p>
                </div>
                <div class="activity-tile">
                    <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Streak Aktif</p><div class="tile-icon tile-icon--green"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg></div></div>
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($leaderboardStats['active_streaks'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400">Pengguna dengan streak berjalan</p>
                </div>
            </div>

            {{-- Top 3 Podium --}}
            <div class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Podium Kehormatan</span>
                        <h4 class="mt-1 text-2xl font-extrabold text-slate-900">Top 3 Overall</h4>
                    </div>
                    <p class="text-xs text-slate-500">Peringkat utama mengikuti total poin</p>
                </div>
                <div x-show="leaderboardTopThree.length === 0" class="glass-card rounded-2xl p-8 text-center text-sm text-slate-500">Belum ada pengguna aktif untuk ditampilkan.</div>
                <div x-show="leaderboardTopThree.length > 0" class="grid items-end gap-4 lg:grid-cols-3">
                    <template x-for="person in leaderboardTopThree" :key="person.id">
                        <article
                            :class="person.rank === 1
                                ? 'lg:order-2 lg:-translate-y-3 border-yellow-300 shadow-lg shadow-yellow-100'
                                : person.rank === 2
                                    ? 'lg:order-1 border-slate-300'
                                    : 'lg:order-3 border-orange-300'"
                            class="glass-card rounded-2xl border-2 p-5 text-center transition-all duration-200 hover:-translate-y-1">
                            <span
                                :class="person.rank === 1 ? 'bg-yellow-400 text-yellow-900' : person.rank === 2 ? 'bg-slate-200 text-slate-700' : 'bg-orange-200 text-orange-800'"
                                class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold"
                                x-text="`RANK #${person.rank}`">
                            </span>
                            <div
                                :class="person.rank === 1
                                    ? 'border-yellow-300 bg-gradient-to-br from-yellow-400 to-orange-400'
                                    : 'border-blue-200 bg-gradient-to-br from-blue-600 to-cyan-500'"
                                class="mx-auto mt-4 flex h-16 w-16 items-center justify-center rounded-full border-4 text-xl font-extrabold text-white"
                                x-text="person.name.split(' ').map(part => part[0]).slice(0, 2).join('').toUpperCase()">
                            </div>
                            <h5 class="mt-3 text-base font-extrabold text-slate-900" x-text="person.name"></h5>
                            <p class="text-xs text-slate-400" x-text="person.username ? `@${person.username}` : 'Pengguna Bugar.in'"></p>
                            <div class="mt-4 grid grid-cols-3 divide-x divide-blue-100 rounded-xl bg-blue-50/60 py-3">
                                <div><strong class="block text-base font-extrabold text-blue-700" x-text="Number(person.total_points).toLocaleString('id-ID')"></strong><span class="text-[10px] text-slate-400">Poin</span></div>
                                <div><strong class="block text-base font-extrabold text-blue-700" x-text="person.attended_sessions_count"></strong><span class="text-[10px] text-slate-400">Sesi</span></div>
                                <div><strong class="block text-base font-extrabold text-blue-700" x-text="`${person.longest_streak}h`"></strong><span class="text-[10px] text-slate-400">Streak</span></div>
                            </div>
                        </article>
                    </template>
                </div>
            </div>

            {{-- Full Rank Table --}}
            <div class="glass-card rounded-2xl p-5 sm:p-7">
                <div class="flex flex-col gap-4 border-b border-blue-100 pb-5 lg:flex-row lg:items-end lg:justify-between">
                    <div><h4 class="text-xl font-extrabold text-slate-900">Peringkat Lengkap Anggota</h4><p class="mt-1 text-xs text-slate-500">Menampilkan hingga 15 pengguna dengan peringkat teratas</p></div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="leaderboardMetric = 'points'" :class="leaderboardMetric === 'points' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'" class="rounded-full px-3 py-2 text-[11px] font-bold transition-colors">Total Poin</button>
                        <button type="button" @click="leaderboardMetric = 'sessions'" :class="leaderboardMetric === 'sessions' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'" class="rounded-full px-3 py-2 text-[11px] font-bold transition-colors">Sesi Kehadiran</button>
                        <button type="button" @click="leaderboardMetric = 'streak'" :class="leaderboardMetric === 'streak' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'" class="rounded-full px-3 py-2 text-[11px] font-bold transition-colors">Streak Terpanjang</button>
                    </div>
                </div>

                <div x-show="leaderboardCurrentUser" class="my-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 p-4 text-white shadow-sm">
                    <div class="flex items-center gap-3"><span class="rounded-lg bg-white/20 px-3 py-2 text-xl font-extrabold" x-text="`#${leaderboardCurrentUser?.rank ?? '—'}`"></span><div><strong class="block text-sm" x-text="leaderboardCurrentUser?.name"></strong><span class="text-xs text-white/80">Posisimu di leaderboard</span></div></div>
                    <span class="text-sm font-bold" x-text="`${Number(leaderboardCurrentUser?.total_points ?? 0).toLocaleString('id-ID')} poin`"></span>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead><tr class="bg-blue-50 text-[10px] font-bold uppercase tracking-wide text-blue-700"><th class="rounded-l-xl px-4 py-3">Peringkat</th><th class="px-4 py-3">Pengguna</th><th class="px-4 py-3 text-right">Total Poin</th><th class="px-4 py-3 text-right">Sesi Hadir</th><th class="rounded-r-xl px-4 py-3 text-right">Streak Terbaik</th></tr></thead>
                        <tbody>
                            <template x-for="person in leaderboardRows.slice(0, 15)" :key="person.id">
                                <tr :class="person.is_current_user ? 'bg-blue-50/60' : 'hover:bg-slate-50/80'" class="border-b border-slate-100 last:border-0">
                                    <td class="px-4 py-3.5 font-extrabold text-blue-600" x-text="String(person.rank).padStart(2, '0')"></td>
                                    <td class="px-4 py-3.5"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-xs font-extrabold text-white" x-text="person.name.split(' ').map(part => part[0]).slice(0, 2).join('').toUpperCase()"></span><div><strong class="block text-sm text-slate-800" x-text="person.name"></strong><span class="text-[11px] text-slate-400" x-text="person.username ? `@${person.username}` : 'Pengguna'"></span></div><span x-show="person.is_current_user" class="rounded bg-blue-600 px-1.5 py-0.5 text-[9px] font-bold text-white">Kamu</span></div></td>
                                    <td class="px-4 py-3.5 text-right font-bold text-slate-800" x-text="Number(person.total_points).toLocaleString('id-ID')"></td>
                                    <td class="px-4 py-3.5 text-right text-slate-600" x-text="person.attended_sessions_count"></td>
                                    <td class="px-4 py-3.5 text-right"><span class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-bold text-cyan-700" x-text="`${person.longest_streak} hari`"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="leaderboardRows.length === 0" class="p-8 text-center text-sm text-slate-500">Belum ada data peringkat.</div>
                </div>
                <p class="mt-5 rounded-xl border border-blue-100 bg-blue-50/60 p-4 text-xs leading-relaxed text-slate-600">Peringkat dihitung dari poin pengguna yang tersimpan. Jumlah sesi dan streak berasal dari data kehadiran serta aktivitas komunitas yang sudah tercatat.</p>
            </div>
        </section>



        {{-- ============================================================
             3. DASHBOARD UTAMA & KOMUNITAS TERBAIK
             ============================================================ --}}
        <section x-show="activeSection === 'komunitas'" x-cloak class="space-y-8">

            {{-- 1. Athlete Personal Header Strip --}}
            <div class="relative overflow-hidden rounded-3xl border border-blue-200/70 bg-gradient-to-br from-white via-blue-50/50 to-sky-50/40 p-6 sm:p-8 shadow-xs">
                <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-blue-300/15 blur-3xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100/70 border border-blue-200 text-blue-700 text-xs font-bold mb-3 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Dashboard Aktivitas Olahraga</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Halo, {{ $user->name ?? 'Pegiat Olahraga' }}! 👋
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl leading-relaxed">
                            Pantau keaktifan komunitasmu, ikuti sesi latihan rutin pekan ini, atau temukan klub olahraga terbaik di sekitarmu.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 bg-white/90 border border-blue-100 rounded-2xl px-3.5 py-2.5 shadow-2xs">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">⚡</span>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">Poin Gerak</span>
                                <strong class="text-xs font-bold text-slate-800">{{ number_format($user->total_points ?? 0, 0, ',', '.') }} PTS</strong>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 bg-white/90 border border-blue-100 rounded-2xl px-3.5 py-2.5 shadow-2xs">
                            <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center font-bold text-sm">🔥</span>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">Streak</span>
                                <strong class="text-xs font-bold text-slate-800">{{ $streakStats['current'] ?? 0 }} Hari</strong>
                            </div>
                        </div>
                        <a href="{{ route('user.explore') }}" class="btn-primary text-xs font-bold py-2.5 px-4 rounded-xl flex items-center gap-1.5 shadow-xs">
                            <span>Eksplor Semua Komunitas</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 2. Komunitas Terbaik & Terpopuler Showcase --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>🏆 Komunitas Terbaik &amp; Terpopuler</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilihan klub teratas berdasarkan status verifikasi dan jumlah anggota terbanyak</p>
                    </div>
                    <a href="{{ route('user.explore') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 group">
                        <span>Buka Direktori ({{ $totalCommunitiesCount }})</span>
                        <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>

                @if($topCommunities->isEmpty())
                    <div class="glass-card rounded-2xl p-10 text-center max-w-md mx-auto">
                        <p class="text-sm font-semibold text-slate-700">Belum ada komunitas terdaftar saat ini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($topCommunities as $comm)
                            @php
                                $commCategory = strtolower($comm->sport_category ?? '');
                                $sportCoverClass = match($commCategory) {
                                    'futsal'    => 'sport-cover-futsal',
                                    'basket', 'basketball' => 'sport-cover-basket',
                                    'badminton' => 'sport-cover-badminton',
                                    'lari', 'running' => 'sport-cover-lari',
                                    'renang', 'swimming' => 'sport-cover-renang',
                                    'yoga'      => 'sport-cover-yoga',
                                    default     => 'sport-cover-default',
                                };
                            @endphp
                            <div class="community-card flex flex-col h-full bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all">
                                {{-- Cover Image --}}
                                <a href="{{ route('user.communities.show', $comm->id) }}" class="community-card__cover {{ $sportCoverClass }} relative h-40 overflow-hidden rounded-t-2xl block group cursor-pointer">
                                    @if($comm->cover_image)
                                        <img src="{{ \Illuminate\Support\Str::startsWith($comm->cover_image, ['http://', 'https://']) ? $comm->cover_image : \Illuminate\Support\Facades\Storage::url($comm->cover_image) }}"
                                             alt="{{ $comm->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             onerror="this.style.display='none'">
                                    @endif
                                    <div class="community-card__overlay"></div>
                                    <span class="community-card__badge">{{ ucfirst($comm->sport_category ?? 'Olahraga') }}</span>
                                    @if($comm->verification_status === 'verified')
                                        <span class="absolute top-3 right-3 bg-blue-600/95 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-xs">
                                            <svg class="w-3 h-3 text-cyan-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Terverifikasi
                                        </span>
                                    @endif
                                </a>

                                <div class="community-card__body p-5 flex flex-col flex-grow justify-between">
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-sm font-bold text-slate-900 leading-snug">
                                                <a href="{{ route('user.communities.show', $comm->id) }}" class="hover:text-blue-600 transition-colors">
                                                    {{ $comm->name }}
                                                </a>
                                            </h4>
                                            @if($comm->city)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    {{ $comm->city }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed">
                                            {{ $comm->description ?? 'Komunitas olahraga aktif yang terbuka untuk anggota baru dan sesi latihan rutin bersama.' }}
                                        </p>
                                    </div>

                                    {{-- Meta: Members, Sesi, Event --}}
                                    <div class="community-card__meta mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ $comm->members_count ?? 0 }} anggota
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $comm->schedules_count ?? 0 }} sesi
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $comm->events_count ?? 0 }} event
                                        </span>
                                    </div>
                                </div>

                                {{-- Card Footer --}}
                                <div class="community-card__footer p-4 bg-slate-50/70 border-t border-slate-100 rounded-b-2xl flex items-center justify-between">
                                    <span class="text-[11px] font-medium text-slate-400">{{ $comm->followers_count ?? 0 }} pengikut</span>
                                    @if(in_array($comm->id, $followedCommunityIds, true))
                                        <form method="POST" action="{{ route('user.communities.unfollow', $comm) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-outline text-[11px] py-1.5 px-3 rounded-lg">✓ Mengikuti</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('user.communities.follow', $comm) }}">
                                            @csrf
                                            <button type="submit" class="btn-primary text-[11px] py-1.5 px-3 rounded-lg">Ikuti Klub</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 3. Agenda & Event Olahraga Mendatang --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Column 1: Event Mendatang --}}
                <div class="glass-card rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                    <span>📅 Event Olahraga Mendatang</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Kompetisi, turnamen, dan fun gathering terbuka</p>
                            </div>
                            <button type="button" @click="setSection('events')" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                                Semua Event &rarr;
                            </button>
                        </div>

                        @if($events->isEmpty())
                            <div class="py-8 text-center text-xs text-slate-400">Belum ada event olahraga mendatang.</div>
                        @else
                            <div class="space-y-3">
                                @foreach($events->take(2) as $ev)
                                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-blue-200 transition-all flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">
                                                {{ $ev->event_date ? \Carbon\Carbon::parse($ev->event_date)->translatedFormat('d M Y') : 'Mendatang' }}
                                            </span>
                                            <h4 class="text-xs font-bold text-slate-900 truncate mt-0.5">{{ $ev->title }}</h4>
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                                {{ $ev->community?->name ?? 'Komunitas' }} &bull; {{ $ev->location ?? 'Lokasi via Maps' }}
                                            </p>
                                        </div>
                                        <button type="button" @click="setSection('events')" class="flex-shrink-0 text-[11px] font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100 cursor-pointer">
                                            Detail
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Column 2: Jadwal Rutin Terdekat --}}
                <div class="glass-card rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                    <span>⏰ Sesi Latihan Rutin Komunitas</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Jadwal latihan mingguan bersama rekan komunitas</p>
                            </div>
                            <button type="button" @click="setSection('agenda')" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                                Buka Kalender &rarr;
                            </button>
                        </div>

                        @if($schedules->isEmpty())
                            <div class="py-8 text-center text-xs text-slate-400">Belum ada jadwal latihan rutin yang diterbitkan.</div>
                        @else
                            <div class="space-y-3">
                                @foreach($schedules->take(2) as $sch)
                                    @php
                                        $schKey = \App\Models\Schedule::class . '-' . $sch->id;
                                        $schParticipant = $userParticipants[$schKey] ?? null;
                                        $schDateFormatted = $sch->date ? $sch->date->locale('id')->isoFormat('ddd, D MMM') : $sch->dayLabel();
                                        $isFull = $sch->capacity !== null && $sch->activeParticipantsCount() >= $sch->capacity;
                                    @endphp
                                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-blue-200 transition-all flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[10px] font-bold text-cyan-600 uppercase tracking-wider">
                                                    {{ $schDateFormatted }} &bull; {{ substr($sch->start_time, 0, 5) }}–{{ substr($sch->end_time, 0, 5) }} WIB
                                                </span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border {{ $sch->levelBadgeClass() }}">
                                                    {{ $sch->levelLabel() }}
                                                </span>
                                                @if($sch->series_id)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[9px] font-bold">
                                                        🔗 Seri
                                                    </span>
                                                @endif
                                                @if($sch->streak_enabled)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[9px] font-bold">
                                                        🔥 Streak
                                                    </span>
                                                @endif
                                            </div>
                                            <h4 class="text-xs font-bold text-slate-900 truncate mt-0.5">{{ $sch->title }}</h4>
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                                {{ $sch->community?->name ?? 'Komunitas' }} &bull; {{ $sch->location ?? 'Lokasi belum ditentukan' }}
                                            </p>
                                        </div>
                                        @if($schParticipant)
                                            <span class="flex-shrink-0 inline-flex items-center gap-1 text-[11px] font-semibold {{ $schParticipant->status === 'waiting_list' ? 'text-amber-700 bg-amber-50' : 'text-emerald-700 bg-emerald-50' }} rounded-lg px-2.5 py-1.5 border {{ $schParticipant->status === 'waiting_list' ? 'border-amber-100' : 'border-emerald-100' }}">
                                                {{ $schParticipant->status === 'waiting_list' ? '⏳ Menunggu' : '✓ Terdaftar' }}
                                            </span>
                                        @elseif($isFull)
                                            <span class="flex-shrink-0 inline-flex items-center text-[10px] font-bold text-slate-500 bg-slate-100 rounded-lg px-2.5 py-1.5 border border-slate-200">
                                                Penuh
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('user.schedules.join', $sch) }}" class="flex-shrink-0">
                                                @csrf
                                                <button type="submit" class="btn-primary text-[11px] py-1.5 px-3 rounded-lg">Ikut</button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 4. Pintasan Cepat Fitur --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div @click="setSection('agenda')" class="glass-card rounded-2xl p-5 border border-slate-200/80 shadow-xs cursor-pointer hover:border-blue-300 transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0 border border-blue-100">
                        ▦
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Agenda Kalender Latihan</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Cek agenda latihan harian, sesi fun match, dan jadwal turnamen.</p>
                    </div>
                </div>
                <div @click="setSection('bmi')" class="glass-card rounded-2xl p-5 border border-slate-200/80 shadow-xs cursor-pointer hover:border-emerald-300 transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0 border border-emerald-100">
                        ⚖️
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Kalkulator Body Mass Index (BMI)</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Hitung status gizi standar Kemenkes RI (Laki-laki &amp; Perempuan) dan berat badan ideal.</p>
                    </div>
                </div>
            </div>

        </section>

        {{-- ============================================================
             4. MODUL 2: EKSPLORASI EVENT
             ============================================================ --}}
        <section x-show="activeSection === 'events'" x-cloak class="space-y-6">
            <div class="glass-card rounded-2xl p-5 sm:p-6 border border-orange-100 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">Eksplorasi Event Mendatang</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Turnamen, fun run, gathering olahraga, dan kompetisi yang akan diselenggarakan</p>
                </div>
                <span class="badge badge--orange self-start sm:self-auto">
                    {{ $events->count() }} Event Tersedia
                </span>
            </div>

            @if($events->isEmpty())
                <div class="glass-card rounded-2xl p-12 text-center max-w-lg mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto mb-4 border border-orange-100 shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-1.5">Belum Ada Event Mendatang</h4>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                        Komunitas belum mempublikasikan event olahraga baru saat ini. Seluruh pengumuman kompetisi dan gathering akan otomatis tampil di sini.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($events as $event)
                        <div class="event-card">
                            {{-- Cover area dengan orange gradient --}}
                            <div class="event-card__cover">
                                @if($event->cover_image ?? false)
                                    <img src="{{ \Illuminate\Support\Str::startsWith($event->cover_image, ['http://', 'https://']) ? $event->cover_image : \Illuminate\Support\Facades\Storage::url($event->cover_image) }}"
                                         alt="{{ $event->title }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'">
                                @endif
                                <span class="event-card__type-badge">{{ $event->community?->name ?? 'Event' }}</span>
                            </div>
                            <div class="p-5 flex flex-col flex-1 gap-3">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="badge badge--orange text-[10px]">{{ $event->community?->sport_category ?? 'Event' }}</span>
                                    <span class="text-xs font-bold {{ $event->registration_fee > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                        {{ $event->registration_fee > 0 ? 'Rp ' . number_format($event->registration_fee, 0, ',', '.') : 'Gratis' }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $event->title }}</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $event->description ?? 'Event olahraga terbuka untuk peserta umum.' }}</p>

                                <div class="mt-3 space-y-1.5 text-xs text-slate-500">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="truncate">{{ $event->location ?? 'Lokasi belum ditentukan' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">Kuota: {{ $event->capacity ?? 'Bebas' }} peserta</span>
                                @php
                                    $morphKey = \App\Models\Event::class . '-' . $event->id;
                                    $eventParticipant = $userParticipants[$morphKey] ?? null;
                                @endphp
                                @if($eventParticipant)
                                    {{-- Tampilkan status saat ini --}}
                                    @php
                                        $statusColor = match($eventParticipant->status) {
                                            'waiting_payment' => 'bg-amber-50 border-amber-200 text-amber-800',
                                            'waiting_list'    => 'bg-blue-50 border-blue-200 text-blue-800',
                                            'registered'      => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                                            'attended'        => 'bg-cyan-50 border-cyan-200 text-cyan-800',
                                            default           => 'bg-slate-50 border-slate-200 text-slate-600',
                                        };
                                        $statusIcon = match($eventParticipant->status) {
                                            'waiting_payment' => '💳',
                                            'waiting_list'    => '⏳',
                                            'registered', 'attended' => '✓',
                                            default => '•',
                                        };
                                    @endphp
                                    <div class="flex flex-col gap-1.5 w-full">
                                        <div class="flex items-center gap-2 rounded-xl border px-3 py-2 text-xs font-semibold {{ $statusColor }}">
                                            <span>{{ $statusIcon }}</span>
                                            <span>{{ $eventParticipant->statusLabel() }}</span>
                                        </div>
                                        @if($eventParticipant->status === 'waiting_payment')
                                            <p class="text-[10px] text-amber-700 leading-relaxed">
                                                Selesaikan pembayaran <strong>Rp {{ number_format($event->registration_fee, 0, ',', '.') }}</strong> kepada penyelenggara untuk konfirmasi pendaftaran.
                                            </p>
                                        @elseif($eventParticipant->status === 'waiting_list')
                                            <p class="text-[10px] text-blue-600 leading-relaxed">
                                                Menunggu konfirmasi dari penyedia komunitas.
                                            </p>
                                        @endif
                                        {{-- Tombol batalkan --}}
                                        @if(in_array($eventParticipant->status, ['waiting_list', 'waiting_payment', 'registered']))
                                            <form method="POST" action="{{ route('user.participation.cancel') }}">
                                                @csrf
                                                <input type="hidden" name="type" value="event">
                                                <input type="hidden" name="id" value="{{ $event->id }}">
                                                <button type="submit" class="text-[11px] text-slate-400 hover:text-red-500 transition-colors underline">
                                                    Batalkan pendaftaran
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                @if($event->isFull())
                                    <span class="text-[11px] font-bold text-slate-400 px-3 py-1.5 rounded-lg bg-slate-100">Penuh</span>
                                @else
                                    <form method="POST" action="{{ route('user.events.join', $event) }}">
                                        @csrf
                                        @if($event->registration_fee > 0)
                                            <button type="submit" class="btn-primary text-[11px] py-1.5 px-3 flex items-center gap-1">
                                                <span>💳</span>
                                                <span>Daftar · Rp {{ number_format($event->registration_fee, 0, ',', '.') }}</span>
                                            </button>
                                        @else
                                            <button type="submit" class="btn-primary text-[11px] py-1.5 px-3">Daftar Event (Gratis)</button>
                                        @endif
                                    </form>
                                @endif
                                @endif
                            </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============================================================
             5. MODUL 3: PENGHITUNG BMI (Kemenkes RI & Mobile-Interactive)
             ============================================================ --}}
        <section x-show="activeSection === 'bmi'" x-cloak class="space-y-5 sm:space-y-6" aria-labelledby="bmi-heading">
            <div class="glass-card rounded-2xl p-4 sm:p-6 lg:p-8 border border-blue-100 shadow-xs">

                {{-- Header Card --}}
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5 sm:mb-7">
                    <div class="max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge badge--green">Standar Kemenkes RI</span>
                            <span class="badge border"
                                  :class="bmiGender === 'l' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-pink-50 text-pink-700 border-pink-200'"
                                  x-text="'Mode: ' + bmiThresholds.genderLabel"></span>
                        </div>
                        <h3 id="bmi-heading" class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-2.5">
                            Kalkulator Body Mass Index (BMI)
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                            Evaluasi indeks massa tubuh (IMT), rentang berat ideal, dan kebutuhan cairan harian yang disesuaikan untuk
                            <strong class="text-slate-700" x-text="bmiThresholds.genderLabel"></strong> menurut acuan Kementerian Kesehatan RI.
                        </p>
                    </div>

                    <button type="button"
                        @click="bmiResetDefault()"
                        class="self-start inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-600 hover:text-blue-600 transition-colors cursor-pointer active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset</span>
                    </button>
                </div>

                {{-- Mobile Live Summary Strip (Langsung terlihat di HP tanpa harus scroll ke bawah) --}}
                <div class="lg:hidden mb-5 rounded-2xl border bg-gradient-to-r p-3.5 flex items-center justify-between gap-3 transition-colors"
                     :class="bmiGender === 'l' ? 'border-blue-100 from-blue-50/80 via-white to-blue-50/40' : 'border-pink-100 from-pink-50/80 via-white to-pink-50/40'">
                    <div class="min-w-0">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Skor IMT Saat Ini</span>
                        <div class="flex items-baseline gap-1.5 mt-0.5">
                            <span class="text-2xl font-extrabold text-slate-900 tabular-nums" x-text="bmiValue"></span>
                            <span class="text-[11px] font-medium text-slate-500">kg/m²</span>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end gap-1">
                        <span :class="[bmiCategory.bg, bmiCategory.color, bmiCategory.border]"
                            class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold border leading-tight"
                            x-text="bmiCategory.label">
                        </span>
                        <span class="text-[10px] text-slate-500 font-medium" x-text="'Ideal: ' + idealWeightRange"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-7 items-start">

                    {{-- ========================================================
                         KOLOM KIRI: FORM INPUT INTERAKTIF & BATAS VALIDASI
                         ======================================================== --}}
                    <div class="lg:col-span-7 space-y-4 sm:space-y-5">

                        {{-- 1. Selector Jenis Kelamin (Tulisan saja, tema Biru & Pink) --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700">Jenis Kelamin</label>
                                <span class="text-[11px] text-slate-400 font-medium">Batas IMT &amp; ideal berbeda</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5" role="group" aria-label="Pilih Jenis Kelamin">
                                <button type="button"
                                    @click="bmiGender = 'l'"
                                    :aria-pressed="bmiGender === 'l'"
                                    :class="bmiGender === 'l'
                                        ? 'bg-blue-50 text-blue-700 border-blue-400 ring-2 ring-blue-100 shadow-sm'
                                        : 'bg-white text-slate-600 border-slate-200 hover:border-blue-200 hover:text-blue-700'"
                                    class="min-h-[52px] px-3 py-2.5 rounded-xl border transition-all cursor-pointer flex flex-col items-center justify-center text-center active:scale-[0.98]">
                                    <span class="block text-xs sm:text-sm font-bold leading-tight">Laki-laki</span>
                                    <span class="block text-[10px] opacity-80 mt-0.5 leading-tight">Ideal 18.0–25.0</span>
                                </button>

                                <button type="button"
                                    @click="bmiGender = 'p'"
                                    :aria-pressed="bmiGender === 'p'"
                                    :class="bmiGender === 'p'
                                        ? 'bg-pink-50 text-pink-700 border-pink-400 ring-2 ring-pink-100 shadow-sm'
                                        : 'bg-white text-slate-600 border-slate-200 hover:border-pink-200 hover:text-pink-700'"
                                    class="min-h-[52px] px-3 py-2.5 rounded-xl border transition-all cursor-pointer flex flex-col items-center justify-center text-center active:scale-[0.98]">
                                    <span class="block text-xs sm:text-sm font-bold leading-tight">Perempuan</span>
                                    <span class="block text-[10px] opacity-80 mt-0.5 leading-tight">Ideal 17.0–23.0</span>
                                </button>
                            </div>
                        </div>

                        {{-- 2. Tinggi Badan (cm) & Berat Badan (kg) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- Tinggi Badan --}}
                            <div class="rounded-2xl border bg-slate-50/60 p-3.5 sm:p-4 space-y-3 transition-colors"
                                 :class="bmiErrors.height ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80'">
                                <div class="flex items-center justify-between gap-2">
                                    <label for="bmi-height-input" class="text-xs font-bold text-slate-700">Tinggi Badan</label>
                                    <span class="text-[11px] font-medium text-slate-400">50–250 cm</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                        @click="bmiStep('bmiHeight', -1, 50, 250, 0)"
                                        :disabled="Number(bmiHeight) <= 50"
                                        aria-label="Kurangi tinggi badan 1 cm"
                                        class="w-11 h-11 min-w-[44px] rounded-xl border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold text-base flex items-center justify-center transition-colors cursor-pointer active:scale-95">
                                        &minus;
                                    </button>
                                    <div class="relative flex-1">
                                        <input id="bmi-height-input"
                                            type="number"
                                            inputmode="decimal"
                                            min="50"
                                            max="250"
                                            step="1"
                                            :value="bmiHeight"
                                            @keydown="bmiBlockInvalidKeys($event, true)"
                                            @input="bmiSanitizeField('bmiHeight', $event, true)"
                                            aria-label="Tinggi badan dalam sentimeter"
                                            class="bmi-number-input glass-input w-full h-11 pl-3 pr-9 text-center text-sm sm:text-base font-extrabold text-slate-900 tabular-nums"
                                            :class="bmiErrors.height ? 'border-rose-400 focus:border-rose-500' : ''">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-slate-400 pointer-events-none">cm</span>
                                    </div>
                                    <button type="button"
                                        @click="bmiStep('bmiHeight', 1, 50, 250, 0)"
                                        :disabled="Number(bmiHeight) >= 250"
                                        aria-label="Tambah tinggi badan 1 cm"
                                        class="w-11 h-11 min-w-[44px] rounded-xl border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold text-base flex items-center justify-center transition-colors cursor-pointer active:scale-95">
                                        +
                                    </button>
                                </div>

                                <div>
                                    <input type="range"
                                        min="50"
                                        max="250"
                                        step="1"
                                        x-model.number="bmiHeight"
                                        aria-label="Slider tinggi badan"
                                        class="bmi-range"
                                        :class="bmiGender === 'p' ? 'bmi-range--female' : ''">
                                    <div class="flex justify-between text-[10px] text-slate-400 font-medium mt-1">
                                        <span>50 cm</span>
                                        <span>150 cm</span>
                                        <span>250 cm</span>
                                    </div>
                                </div>

                                <p x-show="bmiErrors.height" x-cloak class="text-[11px] font-semibold text-rose-600 flex items-center gap-1">
                                    <span>!</span>
                                    <span x-text="bmiErrors.height"></span>
                                </p>
                            </div>

                            {{-- Berat Badan --}}
                            <div class="rounded-2xl border bg-slate-50/60 p-3.5 sm:p-4 space-y-3 transition-colors"
                                 :class="bmiErrors.weight ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80'">
                                <div class="flex items-center justify-between gap-2">
                                    <label for="bmi-weight-input" class="text-xs font-bold text-slate-700">Berat Badan</label>
                                    <span class="text-[11px] font-medium text-slate-400">10–300 kg</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                        @click="bmiStep('bmiWeight', -0.5, 10, 300, 1)"
                                        :disabled="Number(bmiWeight) <= 10"
                                        aria-label="Kurangi berat badan 0.5 kg"
                                        class="w-11 h-11 min-w-[44px] rounded-xl border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold text-base flex items-center justify-center transition-colors cursor-pointer active:scale-95">
                                        &minus;
                                    </button>
                                    <div class="relative flex-1">
                                        <input id="bmi-weight-input"
                                            type="number"
                                            inputmode="decimal"
                                            min="10"
                                            max="300"
                                            step="0.5"
                                            :value="bmiWeight"
                                            @keydown="bmiBlockInvalidKeys($event, true)"
                                            @input="bmiSanitizeField('bmiWeight', $event, true)"
                                            aria-label="Berat badan dalam kilogram"
                                            class="bmi-number-input glass-input w-full h-11 pl-3 pr-9 text-center text-sm sm:text-base font-extrabold text-slate-900 tabular-nums"
                                            :class="bmiErrors.weight ? 'border-rose-400 focus:border-rose-500' : ''">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-slate-400 pointer-events-none">kg</span>
                                    </div>
                                    <button type="button"
                                        @click="bmiStep('bmiWeight', 0.5, 10, 300, 1)"
                                        :disabled="Number(bmiWeight) >= 300"
                                        aria-label="Tambah berat badan 0.5 kg"
                                        class="w-11 h-11 min-w-[44px] rounded-xl border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold text-base flex items-center justify-center transition-colors cursor-pointer active:scale-95">
                                        +
                                    </button>
                                </div>

                                <div>
                                    <input type="range"
                                        min="10"
                                        max="300"
                                        step="0.5"
                                        x-model.number="bmiWeight"
                                        aria-label="Slider berat badan"
                                        class="bmi-range"
                                        :class="bmiGender === 'p' ? 'bmi-range--female' : ''">
                                    <div class="flex justify-between text-[10px] text-slate-400 font-medium mt-1">
                                        <span>10 kg</span>
                                        <span>150 kg</span>
                                        <span>300 kg</span>
                                    </div>
                                </div>

                                <p x-show="bmiErrors.weight" x-cloak class="text-[11px] font-semibold text-rose-600 flex items-center gap-1">
                                    <span>!</span>
                                    <span x-text="bmiErrors.weight"></span>
                                </p>
                            </div>
                        </div>

                        {{-- 3. Usia (Tahun) --}}
                        <div class="rounded-2xl border bg-slate-50/60 p-3.5 sm:p-4 space-y-2.5 transition-colors"
                             :class="bmiErrors.age ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80'">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                                <div>
                                    <label for="bmi-age-input" class="text-xs font-bold text-slate-700">Usia (Tahun)</label>
                                    <p class="text-[11px] text-slate-400">Digunakan untuk kebutuhan cairan AKG &amp; catatan usia (10–100 thn)</p>
                                </div>

                                <div class="flex items-center gap-1.5 sm:w-48">
                                    <button type="button"
                                        @click="bmiStep('bmiAge', -1, 10, 100, 0)"
                                        :disabled="Number(bmiAge) <= 10"
                                        aria-label="Kurangi usia 1 tahun"
                                        class="w-11 h-11 min-w-[44px] rounded-xl border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold text-base flex items-center justify-center transition-colors cursor-pointer active:scale-95">
                                        &minus;
                                    </button>
                                    <div class="relative flex-1">
                                        <input id="bmi-age-input"
                                            type="number"
                                            inputmode="numeric"
                                            min="10"
                                            max="100"
                                            step="1"
                                            :value="bmiAge"
                                            @keydown="bmiBlockInvalidKeys($event, false)"
                                            @input="bmiSanitizeField('bmiAge', $event, false)"
                                            aria-label="Usia dalam tahun"
                                            class="bmi-number-input glass-input w-full h-11 pl-3 pr-9 text-center text-sm sm:text-base font-extrabold text-slate-900 tabular-nums"
                                            :class="bmiErrors.age ? 'border-rose-400 focus:border-rose-500' : ''">
                                        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 pointer-events-none">thn</span>
                                    </div>
                                    <button type="button"
                                        @click="bmiStep('bmiAge', 1, 10, 100, 0)"
                                        :disabled="Number(bmiAge) >= 100"
                                        aria-label="Tambah usia 1 tahun"
                                        class="w-11 h-11 min-w-[44px] rounded-xl border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold text-base flex items-center justify-center transition-colors cursor-pointer active:scale-95">
                                        +
                                    </button>
                                </div>
                            </div>

                            <p x-show="bmiErrors.age" x-cloak class="text-[11px] font-semibold text-rose-600 flex items-center gap-1">
                                <span>!</span>
                                <span x-text="bmiErrors.age"></span>
                            </p>

                            <p x-show="bmiIsUnder18" x-cloak class="text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200/80 rounded-xl px-3 py-2">
                                Catatan: Klasifikasi IMT dewasa Kemenkes ditujukan untuk usia &ge; 18 tahun. Untuk usia di bawah 18 tahun, penilaian gizi menggunakan kurva IMT menurut umur (IMT/U).
                            </p>
                        </div>

                        {{-- 4. Kartu Referensi Kategori Kemenkes RI (Berubah Dinamis Sesuai Gender & Menyala Saat Aktif) --}}
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-2.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="text-xs font-bold text-slate-800">
                                    Kategori Standar Kemenkes RI &mdash;
                                    <span :class="bmiGender === 'l' ? 'text-blue-600' : 'text-pink-600'" x-text="bmiThresholds.genderLabel"></span>
                                </div>
                                <span class="text-[10px] font-medium text-slate-400">Otomatis mengikuti gender</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                                <div class="p-2.5 rounded-xl border transition-all"
                                     :class="bmiCategory.key === 'kurus' ? 'bg-amber-50 border-amber-400 ring-2 ring-amber-100 text-amber-900 font-bold' : 'bg-white border-slate-200/80 text-slate-600'">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold" x-text="bmiThresholds.underText"></span>
                                        <span x-show="bmiCategory.key === 'kurus'" class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    </div>
                                    <div class="mt-0.5 text-[10px] opacity-85">Kurus</div>
                                </div>

                                <div class="p-2.5 rounded-xl border transition-all"
                                     :class="bmiCategory.key === 'normal' ? 'bg-emerald-50 border-emerald-400 ring-2 ring-emerald-100 text-emerald-900 font-bold' : 'bg-white border-emerald-200/70 text-emerald-800'">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold" x-text="bmiThresholds.normalText"></span>
                                        <span x-show="bmiCategory.key === 'normal'" class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    </div>
                                    <div class="mt-0.5 text-[10px] opacity-85">Normal (Ideal)</div>
                                </div>

                                <div class="p-2.5 rounded-xl border transition-all"
                                     :class="bmiCategory.key === 'gemuk' ? 'bg-orange-50 border-orange-400 ring-2 ring-orange-100 text-orange-900 font-bold' : 'bg-white border-slate-200/80 text-slate-600'">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold" x-text="bmiThresholds.overText"></span>
                                        <span x-show="bmiCategory.key === 'gemuk'" class="w-2 h-2 rounded-full bg-orange-500"></span>
                                    </div>
                                    <div class="mt-0.5 text-[10px] opacity-85">Gemuk</div>
                                </div>

                                <div class="p-2.5 rounded-xl border transition-all"
                                     :class="bmiCategory.key === 'obesitas' ? 'bg-red-50 border-red-400 ring-2 ring-red-100 text-red-900 font-bold' : 'bg-white border-slate-200/80 text-slate-600'">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold" x-text="bmiThresholds.obeseText"></span>
                                        <span x-show="bmiCategory.key === 'obesitas'" class="w-2 h-2 rounded-full bg-red-500"></span>
                                    </div>
                                    <div class="mt-0.5 text-[10px] opacity-85">Obesitas</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================================
                         KOLOM KANAN: PANEL HASIL INTERAKTIF & GAUGE
                         ======================================================== --}}
                    <div class="lg:col-span-5">
                        <div class="rounded-2xl p-4 sm:p-6 bg-gradient-to-br border shadow-xs space-y-5 transition-colors"
                             :class="bmiGender === 'l' ? 'from-white via-blue-50/35 to-sky-50/25 border-blue-100' : 'from-white via-pink-50/35 to-rose-50/20 border-pink-100'">

                            {{-- Score + Badge --}}
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Hasil Skrining IMT</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                          :class="bmiGender === 'l' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'"
                                          x-text="bmiThresholds.genderLabel"></span>
                                </div>

                                <div class="flex flex-wrap items-baseline justify-between gap-3 mt-2.5">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight tabular-nums" x-text="bmiValue"></span>
                                        <span class="text-xs font-semibold text-slate-500">kg/m&sup2;</span>
                                    </div>

                                    <span :class="[bmiCategory.bg, bmiCategory.color, bmiCategory.border]"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border"
                                        x-text="bmiCategory.label">
                                    </span>
                                </div>
                            </div>

                            {{-- Interactive Visual Gauge Bar (Segmen Berubah Saat Ganti Gender) --}}
                            <div class="space-y-1.5">
                                <div class="relative pt-3 pb-1">
                                    {{-- Indicator Pin --}}
                                    <div x-show="bmiIsValid"
                                         x-cloak
                                         class="absolute top-0 -translate-x-1/2 transition-all duration-300 ease-out z-10 flex flex-col items-center pointer-events-none"
                                         :style="'left: ' + bmiGaugePercent + '%'">
                                        <span class="w-3 h-3 rounded-full bg-slate-900 border-2 border-white shadow-sm"></span>
                                        <span class="w-0.5 h-3 bg-slate-900/80"></span>
                                    </div>

                                    {{-- 4 Colored Segments --}}
                                    <div class="h-3 w-full rounded-full overflow-hidden flex bg-slate-100 shadow-inner">
                                        <div class="h-full bg-amber-400 transition-all duration-300" :style="'width: ' + bmiGaugeSegments.kurus + '%'" title="Kurus"></div>
                                        <div class="h-full bg-emerald-500 transition-all duration-300" :style="'width: ' + bmiGaugeSegments.normal + '%'" title="Normal"></div>
                                        <div class="h-full bg-orange-400 transition-all duration-300" :style="'width: ' + bmiGaugeSegments.gemuk + '%'" title="Gemuk"></div>
                                        <div class="h-full bg-red-500 transition-all duration-300" :style="'width: ' + bmiGaugeSegments.obesitas + '%'" title="Obesitas"></div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-[10px] font-semibold text-slate-400 tabular-nums">
                                    <span>12</span>
                                    <span class="text-amber-700" x-text="bmiThresholds.underMax.toFixed(1)"></span>
                                    <span class="text-emerald-700 font-bold" x-text="bmiThresholds.normalMax.toFixed(1)"></span>
                                    <span class="text-orange-700" x-text="bmiThresholds.overMax.toFixed(1)"></span>
                                    <span>36+</span>
                                </div>
                            </div>

                            {{-- Selisih ke Berat Ideal --}}
                            <div x-show="bmiWeightDelta" x-cloak
                                 class="rounded-xl px-3.5 py-2.5 text-xs font-semibold border"
                                 :class="{
                                     'bg-emerald-50 border-emerald-200 text-emerald-800': bmiWeightDelta && bmiWeightDelta.tone === 'emerald',
                                     'bg-amber-50 border-amber-200 text-amber-800': bmiWeightDelta && bmiWeightDelta.tone === 'amber',
                                     'bg-orange-50 border-orange-200 text-orange-800': bmiWeightDelta && bmiWeightDelta.tone === 'orange'
                                 }"
                                 x-text="bmiWeightDelta ? bmiWeightDelta.text : ''">
                            </div>

                            {{-- Ringkasan Metrik Spesifik Gender Kemenkes --}}
                            <div class="pt-4 border-t border-slate-200/70 space-y-2.5 text-xs">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500">Rentang Berat Ideal (IMT):</span>
                                    <span class="font-bold text-slate-900 tabular-nums text-right" x-text="idealWeightRange"></span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500">
                                        Target Ideal Broca <span class="text-[10px] text-slate-400" x-text="'(' + bmiThresholds.brocaFormulaText + ')'"></span>:
                                    </span>
                                    <span class="font-bold text-slate-900 tabular-nums text-right" x-text="brocaIdealWeight"></span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500">Batas Lingkar Perut Sehat:</span>
                                    <span class="font-bold text-slate-900 tabular-nums text-right" x-text="bmiThresholds.waistLimit"></span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500">Kebutuhan Air Harian (AKG):</span>
                                    <span class="font-bold text-slate-900 text-right" x-text="bmiWaterRecommendation"></span>
                                </div>
                            </div>

                            {{-- Interactive Save Action --}}
                            <div class="pt-1">
                                <button type="button"
                                    @click="saveBmiHistory()"
                                    :disabled="!bmiIsValid"
                                    class="w-full min-h-[44px] px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer disabled:opacity-45 disabled:cursor-not-allowed flex items-center justify-center gap-1.5 active:scale-[0.98]"
                                    :class="bmiSavedFlash
                                        ? 'bg-emerald-600 text-white shadow-xs'
                                        : (bmiGender === 'l' ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs' : 'bg-pink-600 hover:bg-pink-700 text-white shadow-xs')">
                                    <span x-text="bmiSavedFlash ? '✓ Tersimpan di Tabel Riwayat' : 'Simpan Hasil Cek'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ========================================================
                 TABEL BESAR FULL-WIDTH: RIWAYAT HASIL CEK BMI
                 ======================================================== --}}
            <div class="glass-card rounded-2xl p-4 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h4 class="text-sm sm:text-base font-bold text-slate-900">Riwayat Hasil Cek BMI</h4>
                        <span class="badge badge--blue" x-text="bmiHistory.length + ' tersimpan'"></span>
                    </div>

                    <button type="button"
                        x-show="bmiHistory.length > 0"
                        x-cloak
                        @click="clearBmiHistory()"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 px-3 py-1.5 rounded-xl border border-rose-200/80 bg-rose-50/50 hover:bg-rose-50 transition-colors cursor-pointer">
                        Hapus Semua
                    </button>
                </div>

                {{-- Empty State --}}
                <div x-show="bmiHistory.length === 0"
                     class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 py-8 px-4 text-center">
                    <p class="text-xs sm:text-sm font-medium text-slate-500">
                        Belum ada riwayat cek tersimpan. Tekan tombol <strong class="text-slate-700">Simpan Hasil Cek</strong> di atas untuk mencatat riwayat perhitungan BMI Anda.
                    </p>
                </div>

                {{-- Desktop & Tablet Full Table --}}
                <div x-show="bmiHistory.length > 0" x-cloak class="hidden sm:block overflow-x-auto rounded-xl border border-slate-200/80">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">Waktu Cek</th>
                                <th class="py-3 px-4">Jenis Kelamin</th>
                                <th class="py-3 px-4">Data Tubuh (TB / BB / Usia)</th>
                                <th class="py-3 px-4">Skor IMT</th>
                                <th class="py-3 px-4">Kategori Kemenkes</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs bg-white">
                            <template x-for="item in bmiHistory" :key="item.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4 text-slate-500 whitespace-nowrap" x-text="item.date"></td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                                              :class="item.gender === 'p' ? 'bg-pink-50 text-pink-700 border-pink-200' : 'bg-blue-50 text-blue-700 border-blue-200'"
                                              x-text="item.genderLabel">
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 font-medium tabular-nums whitespace-nowrap"
                                        x-text="item.height + ' cm  •  ' + item.weight + ' kg  •  ' + item.age + ' thn'"></td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="text-sm font-extrabold text-slate-900 tabular-nums" x-text="item.bmi"></span>
                                        <span class="text-[11px] text-slate-400">kg/m&sup2;</span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                                              :class="{
                                                  'bg-amber-50 text-amber-700 border-amber-200': item.categoryKey === 'kurus',
                                                  'bg-emerald-50 text-emerald-700 border-emerald-200': item.categoryKey === 'normal',
                                                  'bg-orange-50 text-orange-700 border-orange-200': item.categoryKey === 'gemuk',
                                                  'bg-red-50 text-red-700 border-red-200': item.categoryKey === 'obesitas'
                                              }"
                                              x-text="item.category">
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button"
                                                @click="loadBmiHistory(item)"
                                                class="px-2.5 py-1.5 rounded-lg border border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-blue-700 text-[11px] font-bold transition-colors cursor-pointer">
                                                Muat
                                            </button>
                                            <button type="button"
                                                @click="removeBmiHistory(item.id)"
                                                class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 text-[11px] font-bold transition-colors cursor-pointer">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Full-Width Cards --}}
                <div x-show="bmiHistory.length > 0" x-cloak class="sm:hidden space-y-2.5">
                    <template x-for="item in bmiHistory" :key="item.id">
                        <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-base font-extrabold text-slate-900 tabular-nums" x-text="item.bmi"></span>
                                        <span class="text-[11px] text-slate-400">kg/m&sup2;</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                              :class="item.gender === 'p' ? 'bg-pink-50 text-pink-700 border-pink-200' : 'bg-blue-50 text-blue-700 border-blue-200'"
                                              x-text="item.genderLabel"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-0.5 tabular-nums"
                                       x-text="item.height + ' cm • ' + item.weight + ' kg • ' + item.age + ' thn'"></p>
                                </div>

                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border flex-shrink-0"
                                      :class="{
                                          'bg-amber-50 text-amber-700 border-amber-200': item.categoryKey === 'kurus',
                                          'bg-emerald-50 text-emerald-700 border-emerald-200': item.categoryKey === 'normal',
                                          'bg-orange-50 text-orange-700 border-orange-200': item.categoryKey === 'gemuk',
                                          'bg-red-50 text-red-700 border-red-200': item.categoryKey === 'obesitas'
                                      }"
                                      x-text="item.category.split(' ')[0]">
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                                <span class="text-[10px] text-slate-400" x-text="item.date"></span>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        @click="loadBmiHistory(item)"
                                        class="min-h-[36px] px-3 py-1 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 text-[11px] font-bold cursor-pointer">
                                        Muat
                                    </button>
                                    <button type="button"
                                        @click="removeBmiHistory(item.id)"
                                        class="min-h-[36px] px-3 py-1 rounded-lg border border-rose-200 bg-white text-rose-600 text-[11px] font-bold cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- ============================================================
             6. MODUL 4: STREAK & POIN USER
             ============================================================ --}}
        <section x-show="activeSection === 'streak'" x-cloak class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-r from-white via-blue-50 to-cyan-50 p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-5">
                    <div>
                        <span class="inline-flex rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-cyan-800">Program Konsistensi Olahraga</span>
                        <h3 class="mt-3 text-3xl font-extrabold tracking-tight text-blue-700 sm:text-4xl">Streak &amp; Poin Olahraga</h3>
                        <p class="mt-2 max-w-3xl text-sm text-blue-700">Pantau rutinitas olahraga, kehadiran komunitas, dan Gerak Poin dari sesi yang tercatat.</p>
                        <p class="mt-2 text-xs font-semibold text-slate-500">Check-in dilakukan lewat QR yang dipindai penyedia komunitas. Daftarkan diri ke jadwal hari ini untuk mendapatkan QR check-in.</p>
                    </div>
                    <div class="min-w-52 rounded-2xl bg-white/90 p-4 shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Status Akun</span>
                        <strong class="mt-1 block text-sm text-blue-700">{{ $user->email_verified_at ? 'Terverifikasi Aktif' : 'Aktif' }}</strong>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="glass-card rounded-2xl border border-blue-100 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Streak Aktif</p><p class="mt-3 text-3xl font-extrabold text-blue-600">{{ number_format($streakStats['current'], 0, ',', '.') }} <span class="text-base">hari</span></p><p class="mt-1 text-xs text-slate-500">Hari berturut-turut dengan kehadiran layak</p></div>
                <div class="glass-card rounded-2xl border border-blue-100 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Gerak Poin</p><p class="mt-3 text-3xl font-extrabold text-cyan-600">{{ number_format($pointsStats['balance'], 0, ',', '.') }} <span class="text-base">PTS</span></p><p class="mt-1 text-xs text-slate-500">Saldo poin akunmu</p></div>
                <div class="glass-card rounded-2xl border border-blue-100 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Sesi Terverifikasi</p><p class="mt-3 text-3xl font-extrabold text-blue-600">{{ number_format($streakStats['sessions'], 0, ',', '.') }} <span class="text-base">sesi</span></p><p class="mt-1 text-xs text-slate-500">Kehadiran dari komunitas yang memenuhi syarat</p></div>
                <div class="glass-card rounded-2xl border border-blue-100 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Rekor Streak</p><p class="mt-3 text-3xl font-extrabold text-cyan-600">{{ number_format($streakStats['longest'], 0, ',', '.') }} <span class="text-base">hari</span></p><p class="mt-1 text-xs text-slate-500">Rekor dari kalender kehadiran layak</p></div>
            </div>

            <div class="glass-card rounded-2xl border border-cyan-100 bg-gradient-to-r from-white via-blue-50/70 to-cyan-50/80 p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Level Pengguna</p>
                        <h4 class="mt-1 text-2xl font-extrabold text-blue-700">Level {{ $levelProgress['level'] }}</h4>
                        <p class="mt-1 text-xs text-slate-600">{{ number_format($levelProgress['total_points'], 0, ',', '.') }} Gerak Poin + {{ number_format($levelProgress['streak_score'], 0, ',', '.') }} skor bonus dari rekor streak {{ $levelProgress['longest_streak'] }} hari.</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-bold text-cyan-700 shadow-sm">{{ number_format($levelProgress['score'], 0, ',', '.') }} skor level</span>
                </div>
                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-white">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-cyan-500 transition-all duration-700" style="width: {{ $levelProgress['progress_percentage'] }}%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-500">{{ number_format($levelProgress['progress_points'], 0, ',', '.') }} / 300 menuju level berikutnya · kurang {{ number_format($levelProgress['points_to_next_level'], 0, ',', '.') }} skor. Bonus streak hanya untuk progres level dan tidak mengurangi saldo poin.</p>
            </div>

            <div class="glass-card rounded-2xl border border-blue-100 bg-white p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div><h4 class="text-lg font-extrabold text-blue-700">Ritme Olahraga Pekan Ini</h4><p class="mt-1 text-xs text-slate-500">{{ $streakStats['active_days_this_week'] }} dari 5 hari target berdasarkan kehadiran komunitas</p></div>
                    @if($todayJoinedSchedule && $todayJoinedSchedule->streak_enabled)
                        @php $tjsId = $todayJoinedSchedule->id; $tjsTitle = $todayJoinedSchedule->title; @endphp
                        <button type="button"
                            @click="qrOpenModal({{ $tjsId }}, '{{ addslashes($tjsTitle) }}')"
                            class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-sm hover:brightness-105">
                            📷 Tampilkan QR Check-in Sesi Hari Ini
                        </button>
                    @elseif($todayJoinedSchedule && !$todayJoinedSchedule->streak_enabled)
                        <p class="text-sm text-slate-500 font-semibold py-2">Sesi hari ini tidak mengaktifkan fitur streak (presensi QR tidak diperlukan).</p>
                    @else
                        <p class="text-sm text-slate-500 font-semibold py-2">Tidak ada sesi terdaftar hari ini. Daftarkan diri ke jadwal untuk mendapatkan QR check-in.</p>
                    @endif
                </div>
                <div class="mt-5 grid grid-cols-4 gap-2 sm:grid-cols-7 sm:gap-3">
                    @foreach($weeklyStreakDays as $day)
                        <div class="rounded-xl {{ $day['completed'] ? 'bg-cyan-100 text-cyan-800' : ($day['is_today'] ? 'bg-blue-600 text-white' : 'bg-blue-50 text-slate-500') }} p-3 text-center">
                            <span class="block text-[10px] font-bold uppercase">{{ $day['label'] }}</span><span class="mx-auto mt-2 flex h-7 w-7 items-center justify-center rounded-full {{ $day['completed'] ? 'bg-cyan-500 text-white' : ($day['is_today'] ? 'bg-white/20 text-white' : 'bg-white text-slate-400') }}">{{ $day['completed'] ? '✓' : ($day['is_today'] ? '⚡' : '·') }}</span>
                            <span class="mt-1 block text-[10px]">{{ $day['completed'] ? 'Tercapai' : ($day['is_today'] ? 'Hari ini' : '—') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,0.85fr)]">
                <div class="glass-card rounded-2xl p-5 sm:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div><h4 class="text-xl font-extrabold text-stone-900">Kalender Konsistensi &amp; Streak Olahraga</h4><p class="mt-1 text-xs text-stone-500">Tanggal bertanda menunjukkan kehadiran yang terverifikasi</p></div>
                        <div class="flex items-center gap-1 rounded-xl bg-blue-50 p-1">
                            <button type="button" @click="streakShiftMonth(-1)" class="rounded-lg px-2.5 py-1.5 text-blue-700 hover:bg-white transition-colors" aria-label="Bulan sebelumnya">‹</button>
                            <span class="min-w-32 px-2 text-center text-xs font-bold text-blue-700" x-text="streakMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })"></span>
                            <button type="button" @click="streakShiftMonth(1)" :disabled="streakMonth.getFullYear() === new Date().getFullYear() && streakMonth.getMonth() >= new Date().getMonth()" class="rounded-lg px-2.5 py-1.5 text-blue-700 hover:bg-white disabled:opacity-40 transition-colors" aria-label="Bulan berikutnya">›</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day"><div class="py-2 text-center text-[10px] font-bold uppercase tracking-wide text-stone-400" x-text="day"></div></template>
                        <template x-for="date in streakCalendarCells()" :key="agendaDateKey(date)">
                            <button type="button" @click="streakSelectedDate = agendaDateKey(date)" :class="[
                                date.getMonth() === streakMonth.getMonth() ? 'bg-blue-50/50 text-slate-700' : 'bg-slate-50/50 text-slate-300',
                                streakSelectedDate === agendaDateKey(date) ? 'ring-2 ring-blue-500 bg-white' : 'hover:bg-blue-100/50',
                                streakActivityOn(date) ? 'border border-emerald-300' : 'border border-transparent'
                            ].join(' ')" class="relative min-h-16 rounded-xl p-2 text-left transition-colors sm:min-h-20">
                                <span class="text-xs font-semibold" x-text="date.getDate()"></span>
                                <span x-show="streakActivityOn(date)" class="absolute right-2 top-2 h-2 w-2 rounded-full bg-emerald-500" aria-label="Ada kehadiran terverifikasi"></span>
                                <span x-show="agendaDateKey(date) === agendaDateKey(new Date())" class="mt-2 block text-[9px] font-bold text-blue-600">Hari ini</span>
                            </button>
                        </template>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-[11px] text-stone-500"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Kehadiran terverifikasi</div>
                </div>

                <div class="space-y-5">
                    <div class="glass-card rounded-2xl p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-bold uppercase tracking-wide text-blue-600">Aktivitas Terverifikasi</span><h4 class="mt-1 text-lg font-extrabold text-slate-900" x-text="new Date(`${streakSelectedDate}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })"></h4></div><span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700" x-text="`${streakActivitiesOnSelectedDate().length} sesi`"></span></div>
                        <div x-show="streakActivitiesOnSelectedDate().length === 0" class="mt-4 rounded-xl bg-stone-50 p-4 text-xs leading-relaxed text-stone-500">Belum ada kehadiran terverifikasi pada tanggal ini. Kehadiran dicatat oleh penyelenggara kegiatan.</div>
                        <div class="mt-4 space-y-3">
                            <template x-for="activity in streakActivitiesOnSelectedDate()" :key="activity.id">
                                <div class="rounded-xl border border-blue-100 bg-white/80 p-4">
                                    <div class="flex items-start justify-between gap-2"><strong class="text-sm text-slate-900" x-text="activity.title"></strong><span class="text-xs font-bold text-blue-600" x-text="activity.time ? `${activity.time} WIB` : ''"></span></div>
                                    <p class="mt-1 text-xs text-stone-500" x-text="activity.community"></p>
                                    <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700" x-text="activity.method === 'qr_scan' ? 'Verifikasi QR' : (activity.method === 'auto' ? 'Verifikasi otomatis' : 'Verifikasi manual')"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-5">
                        <div class="flex items-center justify-between"><h4 class="font-bold text-slate-900">Konsistensi Pekan Ini</h4><strong class="text-sm text-blue-600">{{ $streakStats['active_days_this_week'] }} hari aktif</strong></div>
                        <p class="mt-2 text-xs leading-relaxed text-stone-500">Jumlah hari dalam pekan ini dengan kehadiran terverifikasi. Hadiri sesi komunitas untuk menjaga streak.</p>
                        @if($streakRecords->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($streakRecords as $streak)
                                    <span class="badge badge--blue">{{ ucfirst($streak->sport_category) }} · {{ $streak->current_streak }} hari</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl border border-blue-100 p-5 sm:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div><h4 class="text-xl font-extrabold text-blue-700">Target &amp; Milestone Konsistensi</h4><p class="mt-1 text-xs text-slate-500">Progres berdasarkan kehadiran sesi olahraga yang memenuhi syarat.</p></div>
                    <span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-bold text-cyan-700">{{ $streakStats['current'] }} hari streak aktif</span>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach($streakMilestones as $milestone)
                        <div class="rounded-2xl bg-blue-50 p-4">
                            <div class="flex items-center justify-between gap-3"><strong class="text-sm text-blue-800">{{ $milestone['target'] }} Hari Konsisten</strong><span class="text-xs font-bold text-cyan-700">{{ $milestone['completed'] ? 'Tercapai' : $milestone['progress'] . ' / ' . $milestone['target'] }}</span></div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-cyan-500" style="width: {{ $milestone['percentage'] }}%"></div></div>
                            <p class="mt-2 text-[11px] text-slate-500">{{ $milestone['completed'] ? 'Rekor streak-mu sudah mencapai milestone ini.' : 'Lanjutkan olahraga bersama komunitas untuk membuka milestone.' }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-5 rounded-xl border border-cyan-100 bg-cyan-50 p-4 text-xs leading-relaxed text-cyan-900">Bonus konsistensi mingguan sebesar 100 Gerak Poin diberikan satu kali saat kehadiranmu tercatat pada minimal 5 hari berbeda dalam satu pekan.</p>
            </div>

            <div class="glass-card rounded-2xl border border-blue-100 p-5 sm:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3"><div><h4 class="text-xl font-extrabold text-blue-700">Reward Mitra Olahraga</h4><p class="mt-1 text-xs text-slate-500">Tukar Gerak Poin dengan reward yang tersedia.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-bold text-cyan-700">Saldo {{ number_format($pointsStats['balance'], 0, ',', '.') }} PTS</span></div>
                @if($pointRewards->isEmpty())
                    <p class="mt-5 rounded-xl bg-blue-50 p-5 text-sm text-slate-600">Belum ada reward tersedia. Katalog akan menampilkan reward setelah admin menambahkan mitra dan stoknya.</p>
                @else
                    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($pointRewards as $reward)
                            <article class="flex flex-col justify-between rounded-2xl bg-blue-50 p-4">
                                <div><div class="flex items-start justify-between gap-3"><span class="rounded-lg bg-white p-2 text-xl text-blue-600">🎁</span><strong class="text-sm font-extrabold text-cyan-700">{{ number_format($reward->points_cost, 0, ',', '.') }} PTS</strong></div><h5 class="mt-3 font-bold text-blue-800">{{ $reward->name }}</h5><p class="mt-1 text-xs text-slate-500">{{ $reward->partner ?: 'Mitra Bugar.in' }}</p><p class="mt-2 text-xs leading-relaxed text-slate-600">{{ $reward->description }}</p></div>
                                <div class="mt-4 flex items-center justify-between gap-3"><span class="text-[11px] text-slate-500">Stok {{ $reward->stock }}</span><form method="POST" action="{{ route('user.rewards.redeem', $reward) }}">@csrf<button type="submit" @disabled($pointsStats['balance'] < $reward->points_cost) class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40">Tukar</button></form></div>
                            </article>
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('user.dashboard', ['section' => 'streak']) }}" class="mt-5 inline-block text-xs font-bold text-blue-600">Riwayat poin &amp; penukaran ada di bawah halaman</a>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="glass-card rounded-2xl p-5 sm:p-6">
                    <div class="mb-4 flex items-end justify-between gap-3"><div><h4 class="text-lg font-extrabold text-slate-900">Riwayat Aktivitas Terverifikasi</h4><p class="mt-1 text-xs text-slate-500">Sesi yang tercatat pada tabel kehadiran</p></div><span class="badge badge--blue">{{ $streakStats['sessions'] }} sesi</span></div>
                    @if($verifiedActivities->isEmpty())
                        <div class="rounded-xl bg-stone-50 p-6 text-center text-xs text-stone-500">Belum ada aktivitas dengan kehadiran terverifikasi.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($verifiedActivities->take(5) as $activity)
                                <div class="rounded-xl border border-blue-100 bg-white/80 p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-2"><strong class="text-sm text-stone-900">{{ $activity['title'] }}</strong><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">Terverifikasi</span></div>
                                    <p class="mt-1 text-xs text-stone-500">{{ $activity['community'] }}</p>
                                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-stone-500"><span>{{ \Carbon\Carbon::parse($activity['date'])->translatedFormat('d F Y') }} · {{ $activity['time'] ?? '--:--' }} WIB</span><span>{{ $activity['method'] === 'qr_scan' ? 'QR' : ($activity['method'] === 'auto' ? 'Otomatis' : 'Manual') }}</span></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="glass-card rounded-2xl p-5 sm:p-6">
                    <div class="mb-4 flex items-end justify-between gap-3"><div><h4 class="text-lg font-extrabold text-stone-900">Riwayat Gerak Poin</h4><p class="mt-1 text-xs text-stone-500">Transaksi poin yang tersimpan pada akunmu</p></div><span class="rounded-full bg-cyan-50 px-3 py-1 text-[10px] font-bold text-cyan-700">{{ number_format($pointsStats['earned_this_month'], 0, ',', '.') }} poin bulan ini</span></div>
                    @if($pointsHistory->isEmpty())
                        <div class="rounded-xl bg-stone-50 p-6 text-center text-xs text-stone-500">Belum ada transaksi poin. Poin akan muncul setelah transaksi dicatat pada akunmu.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($pointsHistory as $point)
                                <div class="flex items-start justify-between gap-4 rounded-xl border border-blue-100 bg-white/80 p-4">
                                    <div><strong class="block text-sm text-stone-900">{{ $point->description ?: ucfirst(str_replace('_', ' ', $point->source)) }}</strong><span class="mt-1 block text-[11px] text-stone-500">{{ $point->created_at->translatedFormat('d F Y · H:i') }}</span><span class="mt-1 block text-[10px] text-stone-400">Saldo setelah transaksi: {{ number_format($point->balance_after, 0, ',', '.') }} poin</span></div>
                                    <div><strong class="block text-sm text-stone-900">{{ $point->description ?: ucfirst(str_replace('_', ' ', $point->source)) }}</strong><span class="mt-1 block text-[11px] text-stone-500">{{ $point->created_at->translatedFormat('d F Y · H:i') }}</span><span class="mt-1 block text-[10px] text-stone-400">Saldo setelah transaksi: {{ number_format($point->balance_after, 0, ',', '.') }} poin</span>@if($point->pointable instanceof \App\Models\PointRedemption)<span class="mt-1 block text-[10px] font-bold text-blue-700">Status penukaran: {{ ucfirst($point->pointable->status) }}</span>@endif</div>
                                    <span class="shrink-0 rounded-full {{ $point->points >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }} px-2.5 py-1 text-xs font-extrabold">{{ $point->points > 0 ? '+' : '' }}{{ number_format($point->points, 0, ',', '.') }} pts</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ============================================================
             7. MODUL 5: MENU EDUKASI KESEHATAN
             ============================================================ --}}
       <section x-show="activeSection === 'edukasi'" x-cloak class="space-y-6">
            <div class="glass-card rounded-2xl p-5 sm:p-6 border border-blue-100 shadow-xs flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">Edukasi &amp; Tips Olahraga</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Panduan praktis menjaga performa tubuh, hidrasi, dan pemulihan dari praktisi olahraga</p>
                </div>
                <a href="{{ route('user.edukasi.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Buka Menu Edukasi &rarr;</a>
            </div>

            {{-- Editorial 3-col: variasi ukuran dan warna kartu --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Artikel 1: Tips Latihan — Blue accent --}}
                <div class="glass-card rounded-2xl overflow-hidden border border-blue-100 flex flex-col hover:-translate-y-1 transition-transform duration-200">
                    <div class="h-3 bg-gradient-to-r from-blue-600 to-blue-400"></div>
                    <div class="p-6 flex flex-col flex-1">
                        <span class="badge badge--blue mb-3">Tips Latihan</span>
                        <h4 class="text-base font-bold text-slate-900 leading-snug">
                            Pentingnya Pemanasan Dinamis Sebelum Berolahraga
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed flex-1">
                            Pemanasan dinamis membantu menaikkan suhu inti otot dan melumasi sendi untuk mencegah risiko cedera mendadak saat sesi berintensitas tinggi.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                3 Menit Baca
                            </span>
                            <a href="{{ route('user.edukasi.show', 'pemanasan-sebelum-lari-pagi') }}" class="text-blue-600 font-semibold hover:underline">Baca &rarr;</a>
                        </div>
                    </div>
                </div>

                {{-- Artikel 2: Nutrisi — Emerald accent, slightly taller --}}
                <div class="glass-card rounded-2xl overflow-hidden border border-emerald-100 flex flex-col hover:-translate-y-1 transition-transform duration-200">
                    <div class="h-3 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
                    <div class="p-6 flex flex-col flex-1">
                        <span class="badge badge--green mb-3">Nutrisi &amp; Hidrasi</span>
                        <h4 class="text-base font-bold text-slate-900 leading-snug">
                            Panduan Hidrasi yang Tepat untuk Iklim Tropis
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed flex-1">
                            Keringat berlebih di Indonesia mempercepat kehilangan elektrolit. Minum 250ml air setiap 20 menit berolahraga untuk menjaga fokus dan stamina.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                4 Menit Baca
                            </span>
                            <a href="{{ route('user.edukasi.show', 'panduan-hidrasi-iklim-tropis') }}" class="text-emerald-600 font-semibold hover:underline">Baca &rarr;</a>
                        </div>
                    </div>
                </div>

                {{-- Artikel 3: Pemulihan — Orange accent --}}
                <div class="glass-card rounded-2xl overflow-hidden border border-orange-100 flex flex-col hover:-translate-y-1 transition-transform duration-200">
                    <div class="h-3 bg-gradient-to-r from-orange-400 to-amber-400"></div>
                    <div class="p-6 flex flex-col flex-1">
                        <span class="badge badge--orange mb-3">Pemulihan Otot</span>
                        <h4 class="text-base font-bold text-slate-900 leading-snug">
                            Tidur dan Manajemen Rest Day Berkualitas
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed flex-1">
                            Otot bertumbuh dan memperbaiki seratnya saat beristirahat. Pastikan tidur 7-8 jam per malam untuk menjaga performa konsisten jangka panjang.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                5 Menit Baca
                            </span>
                            <a href="{{ route('user.edukasi.show', 'tidur-dan-manajemen-rest-day') }}" class="text-orange-600 font-semibold hover:underline">Baca &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             8. MODUL 6: RIWAYAT AKTIVITAS USER
             ============================================================ --}}
        <section x-show="activeSection === 'riwayat'" x-cloak class="space-y-6">
            <div class="glass-card rounded-2xl p-5 sm:p-6 border border-blue-100 shadow-xs">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Riwayat Aktivitas &amp; Keanggotaan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar komunitas yang kamu ikuti serta rekap sesi latihan aktif</p>
            </div>

            @if($userMemberships->isEmpty())
                <div class="glass-card rounded-2xl p-12 text-center max-w-lg mx-auto">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center mx-auto mb-4 border border-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-1">Belum Bergabung dengan Komunitas</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        Kamu belum terdaftar di komunitas manapun. Temukan klub lari, basket, atau badminton favoritmu dan mulai berolahraga bersama.
                    </p>
                    <button type="button" @click="setSection('komunitas')" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Cari Komunitas Sekarang
                    </button>
                </div>
            @else
                <div class="glass-card rounded-2xl overflow-hidden border border-blue-100 shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-blue-50/60 border-b border-blue-100 text-slate-700 font-bold">
                                    <th class="py-3.5 px-5">Komunitas</th>
                                    <th class="py-3.5 px-5">Cabor</th>
                                    <th class="py-3.5 px-5">Kota</th>
                                    <th class="py-3.5 px-5">Status Keanggotaan</th>
                                    <th class="py-3.5 px-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($userMemberships as $m)
                                    <tr class="hover:bg-blue-50/30 transition-colors">
                                        <td class="py-3 px-5 font-bold text-slate-900">
                                            {{ $m->community?->name ?? 'Komunitas' }}
                                        </td>
                                        <td class="py-3 px-5">
                                            <span class="badge badge--blue">{{ ucfirst($m->community?->sport_category ?? '-') }}</span>
                                        </td>
                                        <td class="py-3 px-5 text-slate-500">
                                            {{ $m->community?->city ?? '-' }}
                                        </td>
                                        <td class="py-3 px-5">
                                            <span class="badge badge--green badge--pulse">
                                                {{ ucfirst($m->status ?? 'Aktif') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 text-right">
                                            <button type="button"
                                                    @click="openMembershipDetail(
                                                        {{ (int) ($m->community?->id ?? 0) }},
                                                        @js($m->community?->name ?? 'Komunitas'),
                                                        @js(ucfirst($m->community?->sport_category ?? '-')),
                                                        @js($m->community?->city ?? '-'),
                                                        @js(ucfirst($m->status ?? 'Aktif'))
                                                    )"
                                                    class="text-blue-600 hover:text-blue-800 font-semibold text-xs transition-colors cursor-pointer">
                                                Lihat Detail &rarr;
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        {{-- ============================================================
             DETAIL RIWAYAT AKTIVITAS & REKAP SESI MODAL
             ============================================================ --}}
        <div
            x-show="activityDetailModalOpen"
            x-cloak
            @keydown.escape.window="activityDetailModalOpen = false"
            class="fixed inset-0 z-[998] flex items-center justify-center p-4 sm:p-6"
            role="dialog" aria-modal="true" aria-labelledby="activity-detail-modal-title">

            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="activityDetailModalOpen = false"></div>

            <div class="relative z-10 w-full max-w-2xl rounded-3xl bg-white shadow-2xl ring-1 ring-blue-100 overflow-hidden transform transition-all my-auto max-h-[88vh] flex flex-col">
                <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-cyan-500 px-5 sm:px-6 py-4 text-white shrink-0">
                    <div class="min-w-0 pr-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 id="activity-detail-modal-title" class="text-base sm:text-lg font-extrabold text-white truncate" x-text="selectedMembershipDetail?.communityName || 'Detail Aktivitas'"></h2>
                            <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold text-white" x-text="selectedMembershipDetail?.membershipStatus || 'Aktif'"></span>
                        </div>
                        <p class="mt-0.5 text-xs text-blue-100 font-medium">
                            <span x-text="selectedMembershipDetail?.sportCategory || '-'"></span> &bull; <span x-text="selectedMembershipDetail?.city || '-'"></span>
                        </p>
                    </div>
                    <button type="button" @click="activityDetailModalOpen = false" class="rounded-xl p-1.5 text-white/80 hover:bg-white/20 hover:text-white transition shrink-0 cursor-pointer" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 sm:p-6 overflow-y-auto space-y-5">
                    {{-- Rekap singkat --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100 text-center">
                            <span class="text-[11px] font-semibold text-slate-500 block">Total Event Diikuti</span>
                            <span class="text-lg font-extrabold text-slate-900" x-text="selectedMembershipDetail?.activities?.length || 0">0</span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-100 text-center">
                            <span class="text-[11px] font-semibold text-slate-500 block">Sesi Aktif</span>
                            <span class="text-lg font-extrabold text-emerald-700" x-text="selectedMembershipDetail?.activeCount || 0">0</span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-cyan-50/70 border border-cyan-100 text-center">
                            <span class="text-[11px] font-semibold text-slate-500 block">Sudah Hadir</span>
                            <span class="text-lg font-extrabold text-cyan-700" x-text="selectedMembershipDetail?.attendedCount || 0">0</span>
                        </div>
                    </div>

                    {{-- Daftar jadwal / event yang diikuti --}}
                    <div class="space-y-2.5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Jadwal &amp; Event yang Diikuti</h4>

                        <template x-if="!selectedMembershipDetail || selectedMembershipDetail.activities.length === 0">
                            <div class="p-8 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-center space-y-2">
                                <p class="text-sm font-bold text-slate-700">Belum ada jadwal atau event yang diikuti</p>
                                <p class="text-xs text-slate-500">Kamu belum mendaftar ke sesi latihan atau event apa pun di komunitas ini.</p>
                            </div>
                        </template>

                        <template x-for="act in (selectedMembershipDetail?.activities || [])" :key="act.kind + '-' + act.id">
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 hover:bg-blue-50/30 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                              :class="act.kind === 'Event' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700'"
                                              x-text="act.kind"></span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                              :class="act.is_active_session ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
                                              x-text="act.is_active_session ? 'Aktif' : 'Selesai'"></span>
                                    </div>
                                    <h5 class="text-sm font-bold text-slate-900 truncate" x-text="act.title"></h5>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500">
                                        <span x-text="act.date_formatted"></span>
                                        <span x-text="act.time"></span>
                                        <span class="truncate" x-text="act.location"></span>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold"
                                          :class="{
                                              'bg-emerald-100 text-emerald-700': act.status_raw === 'attended',
                                              'bg-blue-100 text-blue-700': act.status_raw === 'registered',
                                              'bg-amber-100 text-amber-700': act.status_raw === 'waiting_list' || act.status_raw === 'waiting_payment'
                                          }"
                                          x-text="act.status_label"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="px-5 sm:px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end shrink-0">
                    <button type="button" @click="activityDetailModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================
             QR CHECK-IN MODAL (User side) — Berada di dalam x-data
             ============================================================ --}}
        <div
            x-show="qrOpen"
            x-cloak
            @keydown.escape.window="qrCloseModal()"
            class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6"
            role="dialog" aria-modal="true" aria-labelledby="qr-modal-title">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="qrCloseModal()"></div>

            {{-- Panel --}}
            <div class="relative z-10 w-full max-w-sm sm:max-w-md rounded-3xl bg-white shadow-2xl ring-1 ring-blue-100 overflow-hidden transform transition-all my-auto max-h-[90vh] flex flex-col">

                {{-- Header --}}
                <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-4 text-white shrink-0">
                    <div class="min-w-0 pr-2">
                        <h2 id="qr-modal-title" class="text-base font-extrabold text-white flex items-center gap-2">
                            <span>📷 QR Code Check-in</span>
                        </h2>
                        <p class="mt-0.5 text-xs text-blue-100 font-semibold truncate" x-text="qrScheduleTitle"></p>
                    </div>
                    <button type="button" @click="qrCloseModal()" class="rounded-xl p-1.5 text-white/80 hover:bg-white/20 hover:text-white transition shrink-0" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body (Scrollable on small mobile screens) --}}
                <div class="p-5 sm:p-6 text-center overflow-y-auto space-y-4">

                    {{-- Loading --}}
                    <div x-show="qrLoading" class="flex flex-col items-center justify-center gap-3 py-10">
                        <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600"></div>
                        <p class="text-xs text-slate-500 font-bold">Membuat QR Check-in Anda...</p>
                    </div>

                    {{-- Error --}}
                    <div x-show="!qrLoading && qrError" class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs sm:text-sm text-rose-700 font-semibold" x-text="qrError"></div>

                    {{-- Success (checked in) --}}
                    <div x-show="!qrLoading && qrCheckedIn" class="flex flex-col items-center gap-4 py-3">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl shadow-xs">
                            ✅
                        </div>
                        <div>
                            <span class="inline-block text-[11px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 tracking-wider mb-1">
                                Kehadiran Terverifikasi
                            </span>
                            <h3 class="text-xl font-black text-slate-900">Check-in Berhasil!</h3>
                            <p class="mt-1 text-xs text-slate-500" x-text="qrScheduleTitle"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 w-full">
                            <div class="rounded-2xl bg-blue-50 border border-blue-100 p-3 text-center">
                                <p class="text-[10px] text-blue-500 font-bold uppercase tracking-wider">Gerak Poin</p>
                                <p class="text-2xl font-black text-blue-700 mt-0.5" x-text="'+' + qrPointsAwarded"></p>
                            </div>
                            <div class="rounded-2xl bg-cyan-50 border border-cyan-100 p-3 text-center">
                                <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-wider">Streak Konsisten</p>
                                <p class="text-2xl font-black text-cyan-700 mt-0.5" x-text="qrCurrentStreak + ' Pekan'"></p>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400">
                            Check-in pada pukul <span class="font-bold text-slate-700" x-text="qrCheckedAt"></span> WIB oleh penyedia komunitas.
                        </p>

                        <button type="button" @click="qrCloseModal(); window.location.reload();"
                                class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 py-3 text-xs font-bold text-white shadow-md hover:brightness-105 transition">
                            Selesai
                        </button>
                    </div>

                    {{-- QR Display --}}
                    <div x-show="!qrLoading && !qrError && !qrCheckedIn" class="flex flex-col items-center gap-3">
                        {{-- QR canvas wrapper (with pulse glow and clean border) --}}
                        <div class="p-3 rounded-2xl bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-200 shadow-inner inline-flex items-center justify-center">
                            <div id="qr-canvas-container" class="bg-white p-2 rounded-xl shadow-xs"></div>
                        </div>

                        {{-- User ID & Name --}}
                        <div class="text-center">
                            <p class="text-sm font-black text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-blue-600 font-bold tracking-wide">ID Peserta: #{{ $user->id }}</p>
                        </div>

                        {{-- Countdown & Rotasi --}}
                        <div class="flex items-center justify-center gap-4 text-xs font-semibold text-slate-600 bg-slate-50 py-2 px-4 rounded-xl border border-slate-200 w-full">
                            <span>Masa Berlaku: <strong class="text-blue-700" x-text="qrExpiresIn + ' dtk'"></strong></span>
                            <span class="text-slate-300">|</span>
                            <span>Rotasi Otomatis: <strong class="text-cyan-700" x-text="qrRotateIn + ' dtk'"></strong></span>
                        </div>

                        {{-- Warning Box --}}
                        <div class="rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-2.5 text-[11px] text-amber-900 font-medium text-left leading-relaxed w-full">
                            ⚠️ <strong>Penting:</strong> Tunjukkan layar ini secara langsung ke kamera penyedia. Jangan membagikan tangkapan layar (*screenshot*) karena kode QR berputar otomatis demi keamanan.
                        </div>

                        {{-- Polling status indicator --}}
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 font-medium">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                            </span>
                            <span>Menunggu penyedia memindai QR...</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    if (typeof QRCode === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"><\/script>');
    }
</script>
@endpush
@endsection

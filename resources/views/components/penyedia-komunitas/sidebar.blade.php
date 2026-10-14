<aside class="fixed top-0 bottom-0 left-0 z-40 w-[260px] bg-white border-r border-gray-200 flex flex-col overflow-y-auto" aria-label="Sidebar Navigation">
    <div class="flex items-center gap-2 px-6 py-6 font-extrabold text-xl text-primary-600 tracking-tight border-b border-gray-100">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-white">
                <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
            </svg>
        </div>
        Bugar.in
    </div>

    <div class="py-6 flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h3 class="px-6 mb-1 text-[0.7rem] font-bold text-primary-600 uppercase tracking-widest">Host Management</h3>
            <nav class="flex flex-col">
                <a href="#" class="flex items-center gap-3 px-6 py-3 text-gray-600 font-medium text-sm transition-all border-l-4 border-transparent hover:bg-gray-50 hover:text-primary-600 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    Overview Komunitas
                </a>
                <a href="#" class="flex items-center gap-3 px-6 py-3 text-gray-600 font-medium text-sm transition-all border-l-4 border-transparent hover:bg-gray-50 hover:text-primary-600 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Kelola Event & RSVP
                </a>
                <a href="#" class="flex items-center gap-3 px-6 py-3 font-medium text-sm transition-all bg-primary-50 text-primary-700 border-l-4 border-primary-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-primary-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Admin Portal
                </a>
            </nav>
        </div>

        <div class="flex flex-col gap-2">
            <h3 class="px-6 mb-1 text-[0.7rem] font-bold text-primary-600 uppercase tracking-widest">Platform Publik</h3>
            <nav class="flex flex-col">
                <a href="{{ route('landing') }}#komunitas" class="flex items-center gap-3 px-6 py-3 text-gray-600 font-medium text-sm transition-all border-l-4 border-transparent hover:bg-gray-50 hover:text-primary-600 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    Jelajah Komunitas
                </a>
                <a href="{{ route('landing') }}#events" class="flex items-center gap-3 px-6 py-3 text-gray-600 font-medium text-sm transition-all border-l-4 border-transparent hover:bg-gray-50 hover:text-primary-600 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    Jadwal & Event
                </a>
                <a href="{{ route('landing') }}#leaderboard" class="flex items-center gap-3 px-6 py-3 text-gray-600 font-medium text-sm transition-all border-l-4 border-transparent hover:bg-gray-50 hover:text-primary-600 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                    </svg>
                    Leaderboard
                </a>
            </nav>
        </div>
    </div>
</aside>

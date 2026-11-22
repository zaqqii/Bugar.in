@extends('layouts.user')

@section('title', 'Edukasi & Tips Olahraga - Bugar.in')

@section('content')
<div class="space-y-6 w-full max-w-7xl">
    <div class="glass-card rounded-2xl p-5 sm:p-6 border border-blue-100 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900">Edukasi &amp; Tips Olahraga</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar artikel dan panduan kesehatan olahraga yang telah diterbitkan.</p>
        </div>
        <a href="{{ route('user.dashboard') }}" class="text-xs font-bold text-blue-600 hover:underline">&larr; Kembali ke Dashboard</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($articles as $article)
            <article class="glass-card rounded-2xl border border-blue-100 p-5 flex flex-col justify-between gap-4">
                <div class="space-y-2">
                    <span class="badge badge--blue text-[10px]">{{ $article->type ?? 'Edukasi' }}</span>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $article->title }}</h3>
                    <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                        {{ $article->excerpt ?: \Illuminate\Support\Str::limit($article->body, 140) }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">
                        {{ $article->published_at?->translatedFormat('d M Y') ?? '' }}
                    </span>
                    <a href="{{ route('user.edukasi.show', $article->slug) }}" class="font-semibold text-blue-600 hover:underline">
                        Baca &rarr;
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full glass-card rounded-2xl p-8 text-center text-xs text-slate-500">
                Belum ada artikel edukasi yang diterbitkan.
            </div>
        @endforelse
    </div>

    @if($articles->hasPages())
        <div class="pt-2">
            {{ $articles->links() }}
        </div>
    @endif
</div>
@endsection
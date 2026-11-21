@extends('layouts.user')

@section('title', $content->title . ' — Edukasi')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 space-y-5">
    <div>
        <a href="{{ route('user.edukasi.index') }}" class="text-xs sm:text-sm font-semibold text-blue-600 hover:underline">
            &larr; Kembali ke Menu Edukasi
        </a>
    </div>

    <article class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-5">
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold">
                {{ $content->type ?? 'Edukasi' }}
            </span>
            @if($content->published_at)
                <span>&bull; Diterbitkan {{ $content->published_at->translatedFormat('d F Y') }}</span>
            @endif
        </div>

        <h1 class="text-xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
            {{ $content->title }}
        </h1>

        @if($content->excerpt)
            <p class="text-sm text-slate-600 leading-relaxed">
                {{ $content->excerpt }}
            </p>
        @endif

        @if($content->cover_image)
            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex justify-center">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->cover_image) }}"
                     alt="{{ $content->title }}"
                     class="w-full max-h-[420px] object-contain">
            </div>
        @endif

        <div class="pt-4 border-t border-slate-100 text-sm text-slate-700 leading-relaxed whitespace-pre-line">
            {{ $content->body }}
        </div>
    </article>
</div>
@endsection
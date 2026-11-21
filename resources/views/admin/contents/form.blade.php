@extends('layouts.admin')

@section('title', $content->exists ? 'Ubah Konten Edukasi' : 'Tambah Konten Edukasi')

@section('content')
<div class="min-h-screen bg-[#0B1120] text-slate-100 p-6 lg:p-8 space-y-6 font-sans">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    {{ $content->exists ? 'Ubah Konten Edukasi' : 'Tambah Konten Edukasi' }}
                </h1>
                <p class="text-xs text-slate-400 mt-1">Slug dibuat otomatis dan dipastikan unik.</p>
            </div>
            <a href="{{ route('admin.contents.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white">
                &larr; Kembali ke Daftar
            </a>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST"
              enctype="multipart/form-data"
              action="{{ $content->exists ? route('admin.contents.update', $content) : route('admin.contents.store') }}"
              class="rounded-2xl bg-[#111827] border border-slate-800 p-6 space-y-5">
            @csrf
            @if($content->exists)
                @method('PUT')
            @endif

            <div>
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Judul <span class="text-rose-400">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $content->title) }}" required
                       class="w-full rounded-xl bg-slate-950 border border-slate-700 text-sm text-white px-3.5 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Jenis Konten <span class="text-rose-400">*</span></label>
                    <select id="type" name="type" required
                            class="w-full rounded-xl bg-slate-950 border border-slate-700 text-sm text-white px-3.5 py-2.5 focus:outline-none focus:border-blue-500">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" @selected(old('type', $content->type ?? 'article') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Status Publikasi <span class="text-rose-400">*</span></label>
                    <select id="status" name="status" required
                            class="w-full rounded-xl bg-slate-950 border border-slate-700 text-sm text-white px-3.5 py-2.5 focus:outline-none focus:border-blue-500">
                        <option value="draft" @selected(old('status', $content->status ?? 'draft') === 'draft')>Draft (Belum Terbit)</option>
                        <option value="published" @selected(old('status', $content->status) === 'published')>Published (Terbit)</option>
                        <option value="archived" @selected(old('status', $content->status) === 'archived')>Archived (Diarsipkan)</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="excerpt" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Ringkasan Singkat</label>
                <textarea id="excerpt" name="excerpt" rows="2"
                          class="w-full rounded-xl bg-slate-950 border border-slate-700 text-sm text-white p-3 focus:outline-none focus:border-blue-500">{{ old('excerpt', $content->excerpt) }}</textarea>
            </div>

            <div>
                <label for="body" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Isi Konten <span class="text-rose-400">*</span></label>
                <textarea id="body" name="body" rows="8" required
                          class="w-full rounded-xl bg-slate-950 border border-slate-700 text-sm text-white p-3 focus:outline-none focus:border-blue-500">{{ old('body', $content->body) }}</textarea>
            </div>

            <div>
                <label for="cover_image" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Cover Image</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp"
                       class="w-full rounded-xl bg-slate-950 border border-slate-700 text-xs text-slate-300 p-2.5">
                @if($content->cover_image)
                    <p class="text-[11px] text-slate-400 mt-1 font-mono">File saat ini: {{ $content->cover_image }}</p>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.contents.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 text-xs font-bold text-slate-300">Batal</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-xs font-bold text-white cursor-pointer">
                    Simpan Konten
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
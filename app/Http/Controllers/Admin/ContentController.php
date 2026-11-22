<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public const TYPES = [
        'article'      => 'Artikel',
        'announcement' => 'Pengumuman',
        'tip'          => 'Tips Latihan',
        'news'         => 'Berita',
    ];

    public function index(Request $request)
    {
        $query = Content::with('author');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('title', 'ilike', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $contents = $query->latest()->paginate(10)->withQueryString();

        return view('admin.contents.index', [
            'contents' => $contents,
            'types'    => self::TYPES,
        ]);
    }

    public function create()
    {
        return view('admin.contents.form', [
            'content' => new Content(),
            'types'   => self::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'type'        => ['required', 'string', 'in:article,announcement,tip,news'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'body'        => ['required', 'string'],
            'status'      => ['required', 'string', 'in:draft,published,archived'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $disk = config('filesystems.default', 'public');
        $coverPath = null;

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('contents/covers', $disk);
        }

        $status = $validated['status'];

        $content = new Content();
        $content->created_by   = Auth::id();
        $content->title        = $validated['title'];
        $content->slug         = Content::generateUniqueSlug($validated['title']);
        $content->excerpt      = $validated['excerpt'] ?? null;
        $content->body         = $validated['body'];
        $content->type         = $validated['type'];
        $content->cover_image  = $coverPath;
        $content->status       = $status;
        $content->published_at = $status === 'published' ? now() : null;
        $content->save();

        return redirect()->route('admin.contents.index')
            ->with('success', 'Konten edukasi berhasil ditambahkan.');
    }

    public function edit(Content $content)
    {
        return view('admin.contents.form', [
            'content' => $content,
            'types'   => self::TYPES,
        ]);
    }

    public function update(Request $request, Content $content)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'type'        => ['required', 'string', 'in:article,announcement,tip,news'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'body'        => ['required', 'string'],
            'status'      => ['required', 'string', 'in:draft,published,archived'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $disk = config('filesystems.default', 'public');
        $coverPath = $content->cover_image;

        if ($request->hasFile('cover_image')) {
            if ($coverPath && Storage::disk($disk)->exists($coverPath)) {
                Storage::disk($disk)->delete($coverPath);
            }
            $coverPath = $request->file('cover_image')->store('contents/covers', $disk);
        }

        $status = $validated['status'];

        $content->title        = $validated['title'];
        $content->slug         = Content::generateUniqueSlug($validated['title'], $content->id);
        $content->excerpt      = $validated['excerpt'] ?? null;
        $content->body         = $validated['body'];
        $content->type         = $validated['type'];
        $content->cover_image  = $coverPath;
        $content->status       = $status;
        $content->published_at = $status === 'published'
            ? ($content->published_at ?? now())
            : null;
        $content->save();

        return redirect()->route('admin.contents.index')
            ->with('success', 'Konten edukasi berhasil diperbarui.');
    }

    public function togglePublish(Content $content)
    {
        $isCurrentlyPublished = $content->status === 'published';

        if ($isCurrentlyPublished) {
            $content->status       = 'draft';
            $content->published_at = null;
            $message = "Konten \"{$content->title}\" ditarik dari publikasi (draft).";
        } else {
            $content->status       = 'published';
            $content->published_at = $content->published_at ?? now();
            $message = "Konten \"{$content->title}\" berhasil diterbitkan.";
        }

        $content->save();

        return back()->with('success', $message);
    }

    public function destroy(Content $content)
    {
        $content->delete();

        return back()->with('success', 'Konten edukasi berhasil dihapus.');
    }
}
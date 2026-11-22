<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function index(Request $request)
    {
        $query = Content::query()
            ->where('status', 'published')
            ->orderByDesc('published_at');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('title', 'ilike', "%{$search}%");
        }

        $articles = $query->paginate(9)->withQueryString();

        return view('user.edukasi.index', compact('articles'));
    }

    public function show(Content $content)
    {
        abort_unless($content->status === 'published', 404);

        return view('user.edukasi.show', compact('content'));
    }
}
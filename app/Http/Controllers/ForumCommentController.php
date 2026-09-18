<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ForumCommentController extends Controller
{
    public function store(Request $request, Forum $forum)
    {
        Gate::authorize('materials.view');

        $request->validate(['isi' => 'required|string']);

        ForumComment::create([
            'forum_id' => $forum->id,
            'user_id' => auth()->id(),
            'isi' => $request->isi,
        ]);

        return redirect()->route('forum.show', $forum)->with('success', 'Komentar ditambahkan.');
    }

    public function destroy(ForumComment $comment)
    {
        Gate::authorize('materials.manage');
        $forum = $comment->forum;
        $comment->delete();

        return redirect()->route('forum.show', $forum)->with('success', 'Komentar dihapus.');
    }
}

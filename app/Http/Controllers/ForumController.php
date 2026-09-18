<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreForumRequest;
use App\Http\Requests\UpdateForumRequest;
use App\Models\Classroom;
use App\Models\Forum;
use App\Models\PenugasanMengajar;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('materials.view');

        $user = $request->user();
        $query = Forum::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'user'])
            ->where('status_aktif', true);

        if ($request->filled('penugasan_mengajar_id')) {
            $query->where('penugasan_mengajar_id', $request->penugasan_mengajar_id);
        }

        // Isolasi
        $this->filterAccess($query, $user);

        $forums = $query->latest()->paginate(15);

        return view('forum.index', compact('forums'));
    }

    public function create(Request $request)
    {
        Gate::authorize('materials.manage');
        $penugasans = $this->getMyPenugasans($request->user());

        return view('forum.create', compact('penugasans'));
    }

    public function store(StoreForumRequest $request)
    {
        Gate::authorize('materials.manage');
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        $penugasan = PenugasanMengajar::findOrFail($validated['penugasan_mengajar_id']);
        $this->authorizePenugasan($penugasan, $request->user());

        Forum::create($validated);

        return redirect()->route('forum.index')->with('success', 'Forum berhasil dibuat.');
    }

    public function show(Forum $forum)
    {
        Gate::authorize('materials.view');
        $this->authorizeAccess($forum, auth()->user());
        $forum->load(['comments.user', 'user']);

        return view('forum.show', compact('forum'));
    }

    public function edit(Forum $forum)
    {
        Gate::authorize('materials.manage');
        $this->authorizeAccess($forum, auth()->user());

        return view('forum.edit', compact('forum'));
    }

    public function update(UpdateForumRequest $request, Forum $forum)
    {
        Gate::authorize('materials.manage');
        $this->authorizeAccess($forum, $request->user());
        $forum->update($request->validated());

        return redirect()->route('forum.show', $forum)->with('success', 'Forum diperbarui.');
    }

    public function destroy(Forum $forum)
    {
        Gate::authorize('materials.manage');
        $this->authorizeAccess($forum, auth()->user());
        $forum->delete();

        return redirect()->route('forum.index')->with('success', 'Forum dihapus.');
    }

    private function getMyPenugasans($user)
    {
        $query = PenugasanMengajar::with(['subject', 'classroom']);
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $query->where('tutor_id', $tutor?->id);
        }

        return $query->where('status_aktif', true)->get();
    }

    private function filterAccess($query, $user)
    {
        if ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('classroom_id', $student?->rombel_id));
        } elseif ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('tutor_id', $tutor?->id));
        } elseif ($user->hasRole('wali-kelas')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $classroomIds = Classroom::where('wali_kelas_id', $tutor?->id)->pluck('id');
            $query->whereHas('penugasanMengajar', fn ($q) => $q->whereIn('classroom_id', $classroomIds));
        }
    }

    private function authorizeAccess($forum, $user)
    {
        $penugasan = $forum->penugasanMengajar;
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if($penugasan->tutor_id !== $tutor?->id, 403);
        } elseif ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            abort_if($penugasan->classroom_id !== $student?->rombel_id, 403);
        }
    }

    private function authorizePenugasan($penugasan, $user)
    {
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if($penugasan->tutor_id !== $tutor?->id, 403);
        }
    }
}

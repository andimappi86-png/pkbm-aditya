<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Program;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->can('announcements.manage')) {
            $query = Announcement::with(['user', 'program', 'classroom'])->latest('published_at');

            // Filters
            if ($request->filled('kategori')) {
                $query->where('kategori', $request->kategori);
            }
            if ($request->filled('status')) {
                if ($request->status === 'draft') {
                    $query->whereNull('published_at');
                } elseif ($request->status === 'published') {
                    $query->whereNotNull('published_at')->where('status_aktif', true);
                }
            }
            if ($request->filled('search')) {
                $query->where('judul', 'like', "%{$request->search}%");
            }

            $announcements = $query->paginate(10)->withQueryString();

            return view('announcements.index', compact('announcements'));
        }

        // Student/Tutor view - filter by target
        $query = Announcement::where('status_aktif', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        $roles = $user->getRoleNames();

        $query->where(function ($q) use ($user, $roles) {
            // Global announcements
            $q->whereNull('target_role')
                ->whereNull('target_program')
                ->whereNull('target_rombel');

            // Role targeted
            if ($roles->isNotEmpty()) {
                $q->orWhereIn('target_role', $roles);
            }

            // Program/Rombel targeted for students
            if ($user->hasRole('peserta-didik') && $user->student) {
                $q->orWhere('target_program', $user->student->program_id)
                    ->orWhere('target_rombel', $user->student->rombel_id);
            }
        });

        if ($request->filled('search')) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        $announcements = $query->latest('published_at')->paginate(10)->withQueryString();

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $this->authorize('announcements.manage');

        $roles = Role::whereNotIn('name', ['kepala-pkbm'])->get();
        $programs = Program::where('status_aktif', true)->get();
        $classrooms = Classroom::where('status_aktif', true)->get();

        return view('announcements.create', compact('roles', 'programs', 'classrooms'));
    }

    public function store(AnnouncementRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        // Handle publishing
        if ($request->has('publish')) {
            $validated['published_at'] = now();
            $validated['status_aktif'] = true;
        } elseif (! $request->filled('published_at')) {
            $validated['published_at'] = null;
        }

        Announcement::create($validated);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show(Announcement $announcement, Request $request)
    {
        $user = $request->user();

        if (! $user->can('announcements.manage')) {
            // Verify student/tutor can see this
            if (! $announcement->status_aktif || ! $announcement->published_at || $announcement->published_at > now()) {
                abort(403);
            }

            // Allow if there are no targets
            if (! $announcement->target_role && ! $announcement->target_program && ! $announcement->target_rombel) {
                return view('announcements.show', compact('announcement'));
            }

            // Check targets
            $allowed = false;

            if ($announcement->target_role && $user->hasRole($announcement->target_role)) {
                $allowed = true;
            }

            if (! $allowed && ($announcement->target_program || $announcement->target_rombel)) {
                if ($user->hasRole('peserta-didik') && $user->student) {
                    $programMatch = ! $announcement->target_program || $announcement->target_program == $user->student->program_id;
                    $rombelMatch = ! $announcement->target_rombel || $announcement->target_rombel == $user->student->rombel_id;

                    if ($programMatch && $rombelMatch) {
                        $allowed = true;
                    }
                }
            }

            if (! $allowed) {
                abort(403);
            }
        }

        return view('announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $this->authorize('announcements.manage');

        $roles = Role::whereNotIn('name', ['kepala-pkbm'])->get();
        $programs = Program::where('status_aktif', true)->get();
        $classrooms = Classroom::where('status_aktif', true)->get();

        return view('announcements.edit', compact('announcement', 'roles', 'programs', 'classrooms'));
    }

    public function update(AnnouncementRequest $request, Announcement $announcement)
    {
        $validated = $request->validated();

        // Handle publishing
        if ($request->has('publish') && ! $announcement->published_at) {
            $validated['published_at'] = now();
            $validated['status_aktif'] = true;
        }

        if (! isset($validated['status_aktif'])) {
            $validated['status_aktif'] = $request->has('status_aktif');
        }

        $announcement->update($validated);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('announcements.manage');
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}

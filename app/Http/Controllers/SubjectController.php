<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('subjects.view');

        $query = Subject::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Filter by status_aktif
        if ($request->filled('status_aktif')) {
            $status = $request->status_aktif === 'true' || $request->status_aktif === '1' ? true : false;
            $query->where('status_aktif', $status);
        }

        $subjects = $query->latest()->paginate(10)->withQueryString();

        return view('subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        Gate::authorize('subjects.create');

        return view('subjects.create');
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return redirect()->route('mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function show(Subject $subject): View
    {
        Gate::authorize('subjects.view', $subject);
        $subject->load('tutors');

        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject): View
    {
        Gate::authorize('subjects.update', $subject);

        return view('subjects.edit', compact('subject'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        Gate::authorize('subjects.update', $subject);
        $subject->update($request->validated());

        return redirect()->route('mata-pelajaran.index')
            ->with('success', 'Data mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        Gate::authorize('subjects.delete', $subject);
        $subject->delete();

        return redirect()->route('mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}

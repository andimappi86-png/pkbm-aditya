<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTutorRequest;
use App\Http\Requests\UpdateTutorRequest;
use App\Models\Tutor;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $query = Tutor::query();

        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $status = $request->get('status_tutor');
        if ($status) {
            $query->where('status_tutor', $status);
        }

        $tutors = $query->latest()->paginate(10)
            ->withQueryString();

        return view('dashboards.tutor.index', [
            'tutors' => $tutors,
            'search' => $search,
            'status' => $status,
            'statusOptions' => ['aktif', 'tidak_aktif'],
        ]);
    }

    public function create()
    {
        return view('dashboards.tutor.create');
    }

    public function store(StoreTutorRequest $request)
    {
        $validated = $request->validated();
        $tutor = Tutor::create($validated);

        return redirect()
            ->route('tutors.index')
            ->with('success', 'Tutor berhasil ditambahkan.');
    }

    public function show(Tutor $tutor)
    {
        return view('dashboards.tutor.show', [
            'tutor' => $tutor,
        ]);
    }

    public function edit(Tutor $tutor)
    {
        return view('dashboards.tutor.edit', [
            'tutor' => $tutor,
        ]);
    }

    public function update(UpdateTutorRequest $request, Tutor $tutor)
    {
        $validated = $request->validated();
        $tutor->update($validated);

        return redirect()
            ->route('tutors.index')
            ->with('success', 'Tutor berhasil diperbarui.');
    }

    public function destroy(Tutor $tutor)
    {
        $tutor->delete();

        return redirect()
            ->route('tutors.index')
            ->with('success', 'Tutor berhasil dihapus.');
    }
}

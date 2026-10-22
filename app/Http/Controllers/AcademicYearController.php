<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('academic-years.view');

        $academicYears = AcademicYear::latest('tanggal_mulai')->get();

        return view('academic-years.index', compact('academicYears'));
    }

    public function create(): View
    {
        $this->authorize('academic-years.manage');

        return view('academic-years.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('academic-years.manage');

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:academic_years,nama',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'status_aktif' => 'nullable|boolean',
        ], [
            'nama.required' => 'Nama tahun ajaran wajib diisi.',
            'nama.unique' => 'Nama tahun ajaran sudah digunakan.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after' => 'Tanggal selesai tidak boleh mendahului tanggal mulai.',
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif');

        if ($validated['status_aktif']) {
            AcademicYear::query()->update(['status_aktif' => false]);
        }

        AcademicYear::create($validated);

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        $this->authorize('academic-years.manage');

        return view('academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('academic-years.manage');

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:academic_years,nama,'.$academicYear->id,
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'status_aktif' => 'nullable|boolean',
        ], [
            'nama.required' => 'Nama tahun ajaran wajib diisi.',
            'nama.unique' => 'Nama tahun ajaran sudah digunakan.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after' => 'Tanggal selesai tidak boleh mendahului tanggal mulai.',
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif');

        if ($validated['status_aktif']) {
            AcademicYear::where('id', '!=', $academicYear->id)->update(['status_aktif' => false]);
        }

        $academicYear->update($validated);

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('academic-years.manage');

        if ($academicYear->classrooms()->exists() || $academicYear->students()->exists() || $academicYear->semesters()->exists()) {
            return back()->with('error', 'Tahun ajaran tidak dapat dihapus karena sudah digunakan.');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}

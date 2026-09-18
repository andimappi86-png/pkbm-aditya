<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\Student;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Letter::with('student');

        if ($user->hasRole('peserta-didik')) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif (! $user->can('letters.manage') && ! $user->can('letters.view')) {
            abort(403);
        }

        // Filters
        if ($request->filled('jenis_surat')) {
            $query->where('jenis_surat', $request->jenis_surat);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('tujuan', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($qs) use ($search) {
                        $qs->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        $letters = $query->latest('tanggal')->paginate(10)->withQueryString();

        return view('letters.index', compact('letters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::orderBy('nama_lengkap')->get();

        return view('letters.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'jenis_surat' => 'required|string',
            'tanggal' => 'required|date',
            'tujuan' => 'nullable|string|max:255',
            'isi' => 'required|string',
            'penandatangan_nama' => 'required|string|max:255',
        ]);

        // Generate nomor surat automatically
        $year = date('Y', strtotime($validated['tanggal']));
        $count = Letter::whereYear('tanggal', $year)->count() + 1;
        $jenis_code = strtoupper(substr($validated['jenis_surat'], 0, 3));
        $validated['nomor_surat'] = "{$count}/PKBM-A/{$jenis_code}/{$year}";

        $validated['penandatangan_id'] = $request->user()->id;

        Letter::create($validated);

        return redirect()->route('letters.index')->with('success', 'Surat berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Letter $letter)
    {
        $this->authorizeView($request->user(), $letter);

        return view('letters.show', compact('letter'));
    }

    /**
     * Print the specified resource.
     */
    public function print(Request $request, Letter $letter)
    {
        $this->authorizeView($request->user(), $letter);

        return view('letters.print', compact('letter'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Letter $letter)
    {
        $students = Student::orderBy('nama_lengkap')->get();

        return view('letters.edit', compact('letter', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Letter $letter)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'jenis_surat' => 'required|string',
            'tanggal' => 'required|date',
            'tujuan' => 'nullable|string|max:255',
            'isi' => 'required|string',
            'penandatangan_nama' => 'required|string|max:255',
        ]);

        $letter->update($validated);

        return redirect()->route('letters.show', $letter)->with('success', 'Surat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Letter $letter)
    {
        $letter->delete();

        return redirect()->route('letters.index')->with('success', 'Surat berhasil dihapus.');
    }

    private function authorizeView($user, Letter $letter)
    {
        if ($user->can('letters.manage') || $user->can('letters.view')) {
            return;
        }

        if ($letter->student_id && $letter->student->user_id === $user->id) {
            return;
        }

        abort(403);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpdbStoreRequest;
use App\Models\AcademicYear;
use App\Models\Ppdb;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PpdbController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->can('admissions.manage') || $user->can('admissions.view')) {
            $query = Ppdb::with(['program', 'academicYear', 'user', 'verifier']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_pendaftaran', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            }

            // Filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('program_id')) {
                $query->where('program_id', $request->program_id);
            }
            if ($request->filled('tahun_ajaran_id')) {
                $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
            }

            $ppdbs = $query->latest()->paginate(10)->withQueryString();

            $programs = Program::where('status_aktif', true)->get();
            $academicYears = AcademicYear::where('status_aktif', true)->get();

            return view('ppdb.index', compact('ppdbs', 'programs', 'academicYears'));
        }

        // For prospective students
        $ppdb = Ppdb::with(['program', 'academicYear'])->where('user_id', $user->id)->first();

        if (! $ppdb) {
            return redirect()->route('ppdb.create');
        }

        return view('ppdb.show', compact('ppdb'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (Ppdb::where('user_id', $request->user()->id)->exists() && ! $request->user()->can('admissions.manage')) {
            return redirect()->route('ppdb.index')->with('info', 'Anda sudah melakukan pendaftaran.');
        }

        $programs = Program::where('status_aktif', true)->get();
        $academicYear = AcademicYear::where('status_aktif', true)->first();

        if (! $academicYear) {
            return redirect()->route('dashboard')->with('error', 'Tidak ada tahun ajaran aktif untuk pendaftaran.');
        }

        return view('ppdb.create', compact('programs', 'academicYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PpdbStoreRequest $request)
    {
        $user = $request->user();

        if (Ppdb::where('user_id', $user->id)->exists() && ! $user->can('admissions.manage')) {
            return redirect()->route('ppdb.index')->with('error', 'Anda sudah melakukan pendaftaran.');
        }

        $validated = $request->validated();

        // Generate nomor pendaftaran
        $year = date('Y');
        $count = Ppdb::whereYear('created_at', $year)->count() + 1;
        $validated['nomor_pendaftaran'] = 'PPDB-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
        $validated['user_id'] = $user->id;
        $validated['status'] = Ppdb::STATUS_PENDING;

        // Handle file uploads
        $fileFields = [
            'dok_ktp_ayah', 'dok_ktp_ibu', 'dok_kk', 'dok_akta_kelahiran',
            'dok_ijasah', 'dok_skhun', 'dok_foto', 'dok_ktp_wali',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('ppdb/dokumen', 'public');
                $validated[$field] = $path;
            }
        }

        $ppdb = Ppdb::create($validated);

        return redirect()->route('ppdb.show', $ppdb)->with('success', 'Pendaftaran berhasil disubmit. Menunggu verifikasi.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Ppdb $ppdb)
    {
        $user = $request->user();

        if ($user->id !== $ppdb->user_id && ! $user->can('admissions.view')) {
            abort(403);
        }

        return view('ppdb.show', compact('ppdb'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ppdb $ppdb)
    {
        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->get();

        return view('ppdb.edit', compact('ppdb', 'programs', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ppdb $ppdb)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'tahun_ajaran_id' => 'required|exists:academic_years,id',
            'nik' => 'required|string|size:16|unique:ppdbs,nik,'.$ppdb->id,
            'nisn' => 'nullable|string|size:10|unique:ppdbs,nisn,'.$ppdb->id,
            'nama_lengkap' => 'required|string|max:255',
            'nama_panggilan' => 'nullable|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|string|max:50',
            'alamat' => 'required|string|max:500',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:ppdbs,email,'.$ppdb->id,
            'nama_ayah' => 'required|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'no_hp_ayah' => 'nullable|string|max:20',
            'nama_ibu' => 'required|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'no_hp_ibu' => 'nullable|string|max:20',
            'nama_wali' => 'nullable|string|max:255',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'no_hp_wali' => 'nullable|string|max:20',
            'alamat_wali' => 'nullable|string|max:500',
        ]);

        // Handle file uploads
        $fileFields = [
            'dok_ktp_ayah', 'dok_ktp_ibu', 'dok_kk', 'dok_akta_kelahiran',
            'dok_ijasah', 'dok_skhun', 'dok_foto', 'dok_ktp_wali',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $request->validate([
                    $field => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
                ]);

                if ($ppdb->$field) {
                    Storage::disk('public')->delete($ppdb->$field);
                }
                $path = $request->file($field)->store('ppdb/dokumen', 'public');
                $validated[$field] = $path;
            }
        }

        $ppdb->update($validated);

        return redirect()->route('ppdb.show', $ppdb)->with('success', 'Data pendaftaran berhasil diupdate.');
    }

    public function verify(Request $request, Ppdb $ppdb)
    {
        $request->validate([
            'status' => 'required|in:diverifikasi,diterima,ditolak',
            'catatan_verifikasi' => 'nullable|string',
        ]);

        $ppdb->update([
            'status' => $request->status,
            'catatan_verifikasi' => $request->catatan_verifikasi,
            'verified_by' => $request->user()->id,
            'tanggal_verifikasi' => now(),
        ]);

        return redirect()->route('ppdb.show', $ppdb)->with('success', 'Status pendaftaran berhasil diupdate.');
    }

    public function bukti(Request $request, Ppdb $ppdb)
    {
        $user = $request->user();
        if ($user->id !== $ppdb->user_id && ! $user->can('admissions.view')) {
            abort(403);
        }

        return view('ppdb.bukti', compact('ppdb'));
    }
}

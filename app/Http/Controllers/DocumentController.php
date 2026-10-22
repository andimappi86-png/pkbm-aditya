<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Document::with(['student.user', 'ppdb']);

        // Role based filtering
        if ($user->hasRole('peserta-didik')) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif (! $user->can('documents.manage') && ! $user->can('documents.view')) {
            abort(403);
        }

        // Filters
        if ($request->filled('jenis_dokumen')) {
            $query->where('jenis_dokumen', $request->jenis_dokumen);
        }

        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_file', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($qs) use ($search) {
                        $qs->where('nama_lengkap', 'like', "%{$search}%");
                    })
                    ->orWhereHas('ppdb', function ($qp) use ($search) {
                        $qp->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        $documents = $query->latest()->paginate(10)->withQueryString();

        return view('documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        $students = collect();
        $defaultMode = 'manual';

        if ($user->can('documents.manage')) {
            $students = Student::orderBy('nama_lengkap')->get();
        } elseif ($user->hasRole('peserta-didik')) {
            $students = Student::where('user_id', $user->id)->get();
            $defaultMode = 'siswa';
        }

        return view('documents.create', compact('students', 'defaultMode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Determine mode
        $mode = $request->input('mode');
        if (! $mode && $user->can('documents.manage') && $request->filled('student_id')) {
            // Backwards compatibility: infer mode from provided fields
            $mode = 'siswa';
        } elseif (! $user->can('documents.manage')) {
            $mode = 'siswa';
        }

        // Validation rules per mode
        $rules = [
            'jenis_dokumen' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
        $messages = [];

        if ($mode === 'siswa') {
            $rules['student_id'] = 'required|exists:students,id';
            $messages['student_id.required'] = 'Silakan pilih peserta didik.';
        } else {
            // Manual mode or mode not provided
            $rules['student_id'] = 'nullable|exists:students,id';
            if (! $mode) {
                $rules['mode'] = 'required|in:siswa,manual';
                $messages['mode.required'] = 'Silakan pilih mode pencatatan dokumen.';
            }
        }

        $request->validate($rules, $messages);

        // Enforce mode-based values
        if ($mode !== 'siswa') {
            $request->merge(['student_id' => null]);
        }

        // Check authorization for specific student
        if (! $user->can('documents.manage') && $request->filled('student_id')) {
            $student = Student::findOrFail($request->student_id);
            if ($student->user_id !== $user->id) {
                abort(403);
            }
        }

        $file = $request->file('file');
        $nama_file = $file->getClientOriginalName();
        $path = $file->store('documents', 'public');

        Document::create([
            'student_id' => $request->student_id,
            'jenis_dokumen' => $request->jenis_dokumen,
            'nama_file' => $nama_file,
            'file_path' => $path,
            'status_verifikasi' => 'pending',
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diupload.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Document $document)
    {
        $this->authorizeView($request->user(), $document);

        return view('documents.show', compact('document'));
    }

    /**
     * Download the specified resource.
     */
    public function download(Request $request, Document $document)
    {
        $this->authorizeView($request->user(), $document);

        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($document->file_path, $document->nama_file);
    }

    /**
     * Verify document (Admin only).
     */
    public function verify(Request $request, Document $document)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:diverifikasi,ditolak',
            'catatan' => 'nullable|string',
        ]);

        $document->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('documents.index')->with('success', 'Status verifikasi dokumen berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Document $document)
    {
        $user = $request->user();
        if (! $user->can('documents.manage')) {
            if ($document->student_id && $document->student?->user_id !== $user->id) {
                abort(403);
            } elseif ($document->ppdb_id && $document->ppdb?->user_id !== $user->id) {
                abort(403);
            }
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }

    private function authorizeView($user, Document $document)
    {
        if ($user->can('documents.manage') || $user->can('documents.view')) {
            return;
        }

        if ($document->student_id && $document->student?->user_id === $user->id) {
            return;
        }

        if ($document->ppdb_id && $document->ppdb?->user_id === $user->id) {
            return;
        }

        abort(403);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\PenugasanMengajar;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('materials.view');

        $user = $request->user();
        $roles = $user->getRoleNames();

        $query = Material::with(['penugasanMengajar.tutor.user', 'penugasanMengajar.subject', 'penugasanMengajar.classroom'])
            ->where('status_aktif', true);

        // Filters
        if ($request->filled('penugasan_mengajar_id')) {
            $query->where('penugasan_mengajar_id', $request->input('penugasan_mengajar_id'));
        }
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->input('tipe'));
        }
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('judul', 'like', "%{$keyword}%");
        }

        // Role-based filtering
        if ($roles->contains('peserta-didik')) {
            $student = $user->student;
            $penugasanIds = ($student && $student->classroom) ? $student->classroom->penugasanMengajar()->where('status_aktif', true)->pluck('id') : [];
            $query->whereIn('penugasan_mengajar_id', $penugasanIds);
        } else {
            $isTutor = $roles->contains('tutor');
            $isWali = $roles->contains('wali-kelas');

            if ($isTutor && $isWali) {
                $tutor = Tutor::where('user_id', $user->id)->first();
                $tutorId = $tutor?->id;
                $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');

                $query->whereHas('penugasanMengajar', function ($q) use ($tutorId, $classroomIds) {
                    $q->where(function ($sub) use ($tutorId, $classroomIds) {
                        if ($tutorId) {
                            $sub->where('tutor_id', $tutorId);
                        }
                        if ($classroomIds->isNotEmpty()) {
                            $sub->orWhereIn('classroom_id', $classroomIds);
                        }
                        if (! $tutorId && $classroomIds->isEmpty()) {
                            $sub->whereRaw('1 = 0');
                        }
                    });
                });
            } elseif ($isTutor) {
                $tutor = Tutor::where('user_id', $user->id)->first();
                abort_if(! $tutor, 403);
                $query->whereHas('penugasanMengajar', fn ($q) => $q->where('tutor_id', $tutor->id));
            } elseif ($isWali) {
                $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
                $query->whereHas('penugasanMengajar', fn ($q) => $q->whereIn('classroom_id', $classroomIds));
            }
        }

        $materials = $query->orderBy('urutan')->latest()->paginate(15)->withQueryString();

        // For filter dropdown
        $penugasans = $this->getPenugasansForUser($user, $roles);

        return view('materials.index', compact('materials', 'penugasans'));
    }

    public function create(Request $request)
    {
        Gate::authorize('materials.manage');

        $user = $request->user();
        $roles = $user->getRoleNames();

        $penugasans = $this->getPenugasansForUser($user, $roles);

        return view('materials.create', compact('penugasans'));
    }

    public function store(StoreMaterialRequest $request)
    {
        Gate::authorize('materials.manage');

        $validated = $request->validated();
        $user = $request->user();

        $penugasan = PenugasanMengajar::findOrFail($validated['penugasan_mengajar_id']);
        $this->authorizePenugasan($penugasan, $user);

        if ($request->hasFile('file_path')) {
            $path = $request->file('file_path')->store('materials', 'public');
            $validated['file_path'] = $path;
        }

        Material::create($validated);

        return redirect()->route('materi.index')->with('success', 'Materi berhasil dibuat.');
    }

    public function show(Material $material)
    {
        Gate::authorize('materials.view');
        $this->authorizeMaterial($material, auth()->user());

        return view('materials.show', compact('material'));
    }

    public function edit(Material $material)
    {
        Gate::authorize('materials.manage');
        $this->authorizeMaterial($material, auth()->user());

        $penugasans = $this->getPenugasansForUser(auth()->user(), auth()->user()->getRoleNames());

        return view('materials.edit', compact('material', 'penugasans'));
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        Gate::authorize('materials.manage');
        $this->authorizeMaterial($material, $request->user());

        $validated = $request->validated();

        if ($request->hasFile('file_path')) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $path = $request->file('file_path')->store('materials', 'public');
            $validated['file_path'] = $path;
        }

        $material->update($validated);

        return redirect()->route('materi.index')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material)
    {
        Gate::authorize('materials.manage');
        $this->authorizeMaterial($material, auth()->user());

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('materi.index')->with('success', 'Materi berhasil dihapus.');
    }

    private function getPenugasansForUser($user, $roles)
    {
        $query = PenugasanMengajar::with(['tutor', 'subject', 'classroom'])->where('status_aktif', true);

        if ($roles->contains('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            if ($tutor) {
                $query->where('tutor_id', $tutor->id);
            }
        }

        return $query->get();
    }

    private function authorizePenugasan(PenugasanMengajar $penugasan, $user): void
    {
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor || $penugasan->tutor_id !== $tutor->id, 403);
        }
    }

    private function authorizeMaterial(Material $material, $user): void
    {
        $material->loadMissing('penugasanMengajar');

        if ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            abort_if(! $student || ! $student->classroom || $material->penugasanMengajar->classroom_id !== $student->classroom->id, 403);

            return;
        }

        $isTutor = $user->hasRole('tutor');
        $isWali = $user->hasRole('wali-kelas');

        if ($isTutor || $isWali) {
            $canAccessAsTutor = false;
            $canAccessAsWali = false;

            if ($isTutor) {
                $tutor = Tutor::where('user_id', $user->id)->first();
                $canAccessAsTutor = $tutor && $material->penugasanMengajar->tutor_id === $tutor->id;
            }

            if ($isWali) {
                $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
                $canAccessAsWali = $classroomIds->contains($material->penugasanMengajar->classroom_id);
            }

            abort_if(! $canAccessAsTutor && ! $canAccessAsWali, 403);
        }
    }
}

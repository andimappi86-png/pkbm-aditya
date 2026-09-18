<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Requests\UpdateSubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Tutor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function store(StoreSubmissionRequest $request)
    {
        Gate::authorize('assignments.submit');

        $validated = $request->validated();
        $user = $request->user();
        $student = $user->student;

        abort_if(! $student, 403, 'Akun ini tidak terhubung dengan data siswa.');

        $assignment = Assignment::findOrFail($validated['assignment_id']);

        // Pastikan tugas di kelas siswa
        abort_if(
            ! $student->classroom || $assignment->penugasanMengajar->classroom_id !== $student->classroom->id,
            403,
            'Tugas ini tidak tersedia untuk rombel Anda.'
        );

        // Pastikan belum mengumpulkan
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['assignment_id' => 'Anda sudah mengumpulkan tugas ini.'])->withInput();
        }

        if ($request->hasFile('file_jawaban')) {
            $path = $request->file('file_jawaban')->store('submissions', 'public');
            $validated['file_jawaban'] = $path;
        }

        Submission::create([
            ...$validated,
            'student_id' => $student->id,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        return redirect()->route('tugas.show', $assignment)->with('success', 'Tugas berhasil dikumpulkan.');
    }

    public function update(UpdateSubmissionRequest $request, Submission $submission)
    {
        $user = $request->user();

        // Siswa can edit own submission (before graded)
        if ($user->hasRole('peserta-didik')) {
            Gate::authorize('assignments.submit');
            abort_if(! $user->student || $submission->student_id !== $user->student->id, 403);
            abort_if($submission->status === 'graded', 403, 'Tugas sudah dinilai dan tidak dapat diubah.');

            $validated = $request->validated();
            unset($validated['feedback'], $validated['nilai']);

            if ($request->hasFile('file_jawaban')) {
                if ($submission->file_jawaban) {
                    Storage::disk('public')->delete($submission->file_jawaban);
                }
                $validated['file_jawaban'] = $request->file('file_jawaban')->store('submissions', 'public');
            }

            $submission->update($validated);

            return redirect()->route('tugas.show', $submission->assignment)->with('success', 'Pengumpulan berhasil diperbarui.');
        }

        // Tutor/Admin can grade
        Gate::authorize('assignments.manage');
        $this->authorizeSubmission($submission, $user);

        $validated = $request->validated();

        $submission->update([
            ...$validated,
            'status' => 'graded',
        ]);

        return redirect()->route('tugas.show', $submission->assignment)->with('success', 'Nilai dan feedback berhasil disimpan.');
    }

    public function destroy(Submission $submission)
    {
        Gate::authorize('assignments.manage');
        $this->authorizeSubmission($submission, auth()->user());

        if ($submission->file_jawaban) {
            Storage::disk('public')->delete($submission->file_jawaban);
        }

        $submission->delete();

        return redirect()->route('tugas.show', $submission->assignment)->with('success', 'Pengumpulan berhasil dihapus.');
    }

    private function authorizeSubmission(Submission $submission, $user): void
    {
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor || $submission->assignment->penugasanMengajar->tutor_id !== $tutor->id, 403);
        }
    }
}

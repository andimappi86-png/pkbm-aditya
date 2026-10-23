<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        Gate::authorize('programs.view');

        $programs = Program::where('status_aktif', true)
            ->withCount(['students', 'classrooms'])
            ->get();

        return view('programs.index', compact('programs'));
    }

    public function show(Program $program): View
    {
        Gate::authorize('programs.view');

        $program->load(['classrooms' => function ($q) {
            $q->where('status_aktif', true);
        }]);

        return view('programs.show', compact('program'));
    }
}

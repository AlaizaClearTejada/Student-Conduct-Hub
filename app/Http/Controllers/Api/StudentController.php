<?php

namespace App\Http\Controllers\Api;

use App\Helpers\StudentProgramCatalog;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a paginated listing of students.
     * Required Role: Staff, Administrator
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $students = User::query()
            ->role('student')
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('student_id', 'like', "%{$search}%"))
            ->when($request->college, fn ($q, $college) => $q->where('college', $college))
            ->when($request->program, fn ($q, $program) => $q->where('program', $program))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return StudentResource::collection($students);
    }

    /**
     * Register a new student profile.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'unique:users,student_id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'program' => ['required', 'string', Rule::in(StudentProgramCatalog::programsForCollege($request->input('college')))],
            'college' => ['required', 'string', Rule::in(StudentProgramCatalog::colleges())],
            'year_level' => ['sometimes', 'integer', 'min:1', 'max:6'],
            'section' => ['sometimes', 'string', 'max:50'],
        ]);

        $student = User::create([
            'name' => $validated['first_name'].' '.$validated['last_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(16)), // Temporary password
            'student_id' => $validated['student_id'],
            'program' => $validated['program'],
            'college' => $validated['college'],
            'role_type' => 'student',
            'year_level' => $validated['year_level'] ?? null,
            'section' => $validated['section'] ?? null,
        ]);

        $student->assignRole('student');

        return response()->json([
            'message' => 'Student registered successfully',
            'student' => new StudentResource($student),
        ], 201);
    }

    /**
     * Display the specified student.
     */
    public function show(User $student): JsonResponse
    {
        if (! $student->hasRole('student')) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'student' => new StudentResource($student),
        ]);
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, User $student): JsonResponse
    {
        if (! $student->hasRole('student')) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $validated = $request->validate([
            'student_id' => ['sometimes', 'string', 'unique:users,student_id,'.$student->id],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$student->id],
            'program' => ['required_with:college', 'string', Rule::in(StudentProgramCatalog::programsForCollege($request->input('college', $student->college)))],
            'college' => ['sometimes', 'string', Rule::in(StudentProgramCatalog::colleges())],
            'year_level' => ['sometimes', 'integer', 'min:1', 'max:6'],
            'section' => ['sometimes', 'string', 'max:50'],
        ]);

        if (isset($validated['first_name']) || isset($validated['last_name'])) {
            $firstName = $validated['first_name'] ?? $student->first_name;
            $lastName = $validated['last_name'] ?? $student->last_name;
            $validated['name'] = $firstName.' '.$lastName;
        }

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => new StudentResource($student->fresh()),
        ]);
    }

    /**
     * Remove the specified student.
     */
    public function destroy(User $student): JsonResponse
    {
        if (! $student->hasRole('student')) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully',
        ]);
    }
}

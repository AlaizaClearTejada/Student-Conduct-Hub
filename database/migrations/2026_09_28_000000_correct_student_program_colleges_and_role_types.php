<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $collegeByProgram = [
            'BSIT' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            'BSBA' => 'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY',
            'BSED' => 'COLLEGE OF TEACHER EDUCATION',
        ];

        foreach ($collegeByProgram as $program => $college) {
            DB::table('users')
                ->whereNotNull('student_id')
                ->where('program', $program)
                ->where(function ($query) use ($college) {
                    $query->whereNull('college')->orWhere('college', '<>', $college);
                })
                ->update(['college' => $college]);
        }

        $studentRoleUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('model_has_roles.model_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('roles.name', 'student');

        DB::table('users')
            ->where('role_type', 'osdw_staff')
            ->where(function ($query) use ($studentRoleUserIds) {
                $query->whereNotNull('student_id')->orWhereIn('id', $studentRoleUserIds);
            })
            ->update(['role_type' => 'student']);
    }

    public function down(): void {}
};

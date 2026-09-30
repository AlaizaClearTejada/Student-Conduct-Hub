<?php

namespace App\Livewire\Staff;

use App\Imports\StudentImport;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class StudentRoster extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public bool $showImportModal = false;

    public $importFile;

    public ?string $importResult = null;

    public ?string $importError = null;

    public ?array $previewValidRows = null;

    public ?int $previewSkippedCount = null;

    /** @var array<int, array{row: int, student_id: string, reason: string}> */
    public array $importErrors = [];

    public bool $showArchived = false;

    /**
     * Reset pagination when search input changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when toggle changes.
     */
    public function updatedShowArchived(): void
    {
        $this->resetPage();
    }

    /**
     * Parse the file when it is uploaded.
     */
    public function updatedImportFile(): void
    {
        $this->reset(['importResult', 'importError', 'importErrors', 'previewValidRows', 'previewSkippedCount']);

        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new StudentImport;
            Excel::import($import, $this->importFile->getRealPath());

            $this->previewValidRows = $import->validRows;
            $this->previewSkippedCount = $import->skippedCount;
            $this->importErrors = $import->errors;
        } catch (\Exception $e) {
            $this->importError = 'File parsing failed: '.$e->getMessage();
            $this->previewValidRows = null;
        }
    }

    /**
     * Navigate to view student profile.
     */
    public function viewStudent(int $userId): void
    {
        $this->redirect(route('staff.students.show', $userId), navigate: true);
    }

    /**
     * Navigate to create student form.
     */
    public function openCreateForm(): void
    {
        $this->redirect(route('staff.students.create'), navigate: true);
    }

    /**
     * Navigate to edit student form.
     */
    public function openEditForm(int $userId): void
    {
        $this->redirect(route('staff.students.edit', $userId), navigate: true);
    }

    /**
     * Archive the specified student.
     */
    public function archiveStudent(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->delete(); // Triggers soft delete

        // Flash message or dispatch event if you want feedback, but livewire re-renders automatically
        // $this->dispatch('student-archived');
    }

    /**
     * Open the import modal.
     */
    public function openImportModal(): void
    {
        $this->reset(['importFile', 'importResult', 'importError', 'importErrors', 'previewValidRows', 'previewSkippedCount']);
        $this->showImportModal = true;
    }

    /**
     * Close the import modal.
     */
    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'importResult', 'importError', 'importErrors', 'previewValidRows', 'previewSkippedCount']);
    }

    /**
     * Confirm and import the previewed students.
     */
    public function confirmImport(): void
    {
        if (empty($this->previewValidRows)) {
            $this->importError = 'No valid records to import.';

            return;
        }

        // Extend execution time limit for bulk hashing (if permitted by server config)
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        try {
            $importedCount = 0;

            foreach ($this->previewValidRows as $row) {
                // Double check it doesn't exist just in case (including archived)
                if (User::withTrashed()->where('student_id', $row['student_id'])->exists() || User::withTrashed()->where('email', $row['email'])->exists()) {
                    continue;
                }

                // Use their Student ID as the default password for convenience, or fallback to random
                $plainPassword = $row['student_id'] ?: \Illuminate\Support\Str::random(10);
                $row['password'] = \Illuminate\Support\Facades\Hash::make($plainPassword);

                $user = User::create($row);
                $user->assignRole('student');

                // Queue the automated welcome email
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\StudentWelcomeMail($user, $plainPassword));

                $importedCount++;
            }

            if ($importedCount > 0) {
                $this->importResult = "Successfully imported {$importedCount} student(s).";
                if ($this->previewSkippedCount > 0) {
                    $this->importResult .= " Skipped {$this->previewSkippedCount} duplicate(s)/empty rows.";
                }
            } else {
                $this->importResult = 'No new students imported. All valid records became duplicates during confirmation.';
            }

            $this->importError = null;
            $this->reset(['importFile', 'previewValidRows', 'previewSkippedCount', 'importErrors']);
        } catch (\Exception $e) {
            $this->importError = 'Import failed: '.$e->getMessage();
        }
    }

    /**
     * Restore the specified archived student.
     */
    public function restoreStudent(int $userId): void
    {
        $user = User::onlyTrashed()->findOrFail($userId);
        $user->restore(); // Triggers restore
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        $query = User::role('student');

        if ($this->showArchived) {
            $query = User::onlyTrashed()->role('student');
        }

        $students = $query
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('student_id', 'like', '%'.$this->search.'%')
                        ->orWhere('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('college', 'like', '%'.$this->search.'%')
                        ->orWhere('section', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        return view('livewire.staff.student-roster', compact('students'));
    }
}

# Tribunal Module, Document OCR & Case Investigation Pipeline

This document details the architecture, implementation, and user workflows for the **Tribunal Module**, **Scanned Document OCR Processing**, and the **Case Investigation Progress Tracker** in the Student Conduct Management System (SCMS).

---

## 1. Role-Based Dynamic Routing & UI Visibility

To protect confidential hearing records and restrict sensitive tribunal operations, access is strictly governed by user role types.

### Access Levels

| Account Type | Permissions & Access Scope | Tribunal Module Visibility |
| :--- | :--- | :--- |
| **Ordinary OSDW Staff** | Access limited to general student records, student list roster, monthly incident reports, and student clearance processing. | ❌ **Hidden / Restricted** |
| **Tribunal Panel Member** | Full access to confidential tribunal hearing files, resolution uploads, OCR text extraction, and case status transitions (`role_type: tribunal_panel`). | ✅ **Visible in Sidebar** |

### Implementation Details

* **Route Authorization Middleware:** [`App\Http\Middleware\EnsureTribunalAccess`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Middleware/EnsureTribunalAccess.php) checks `auth()->user()->role_type === 'tribunal_panel'`. Requests from unauthorized users result in an HTTP `403 Forbidden` response.
* **Isolated Route Group:** Tribunal routes are registered in [`routes/web.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/routes/web.php#L123-L130) under the `dashboard/tribunal` prefix protected by the `tribunal` middleware alias.
* **Sidebar Conditional UI Rendering:** In [`resources/views/layouts/admin.blade.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/resources/views/layouts/admin.blade.php#L74-L82), the **Tribunal Module** navigation tab is wrapped inside:
  ```blade
  @if(auth()->check() && auth()->user()->role_type === 'tribunal_panel')
      <a href="{{ route('tribunal.cases.index') }}" class="...">Tribunal Module</a>
  @endif
  ```

---

## 2. Resolution & Document Upload Workflow via OCR

The resolution workflow allows tribunal members to conduct offline panel hearings, draft and sign hardcopy reports, and integrate scanned files into the database with searchable text extraction.

```
[Offline Tribunal Meeting] 
       │ (Draft & Sign Hardcopy)
       ▼
[Scan Document (PDF/Image)] ────────► [Upload via System Portal]
                                            │
                                            ▼
                               [TribunalDocumentController]
                                            │ (Save File & Create TribunalCase)
                                            ▼
                               [Queue Job: ProcessDocumentOcr]
                                            │ (Run ocr_processor.py)
                                            ▼
                             [OpenCV & PyTesseract OCR Engine]
                                            │ (Extract Text)
                                            ▼
                             [Update searchable_text in DB]
```

### Technical Workflow Components

1. **Upload Handling Controller:** [`App\Http\Controllers\TribunalDocumentController`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Controllers/TribunalDocumentController.php) validates `.pdf`, `.png`, `.jpeg`, and `.jpg` uploads up to 20MB, saves them to `storage/app/tribunal_documents`, creates the [`TribunalCase`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Models/TribunalCase.php) record, and dispatches the background OCR job.
2. **Asynchronous Background Processing:** [`App\Jobs\ProcessDocumentOcr`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Jobs/ProcessDocumentOcr.php) executes asynchronously via Laravel's queue worker to ensure the web user interface remains fast and non-blocking.
3. **Python OCR Engine:** [`scripts/ocr_processor.py`](file:///c:/MyXampp/htdocs/student-conduct-management-system/scripts/ocr_processor.py) utilizes OpenCV (`cv2`) for image grayscale conversion, Gaussian blur filtering, and Otsu binarization thresholds before extracting text using `pytesseract`.
4. **Side-by-Side Review Interface:** [`resources/views/tribunal/cases/show.blade.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/resources/views/tribunal/cases/show.blade.php) presents a dual-view pane displaying the original scanned resolution file alongside the extracted searchable text block.

---

## 3. Process of Investigation Progress Tracker

Case hearings follow a structured state machine model ensuring step-by-step investigation tracking.

### Pipeline Stages

```
[Under Informal Discussion] ──► [Under Formal Investigation] ──► [Resolution Drafted] ──► [Case Resolved]
```

| Pipeline Stage | Enum Value | Description |
| :--- | :--- | :--- |
| **Under Informal Discussion** | `under_informal_discussion` | Initial case submission and preliminary panel assessment. |
| **Under Formal Investigation** | `under_formal_investigation` | Active hearing, evidence collection, and panel deliberation. |
| **Resolution Drafted** | `resolution_drafted` | Decision formulated; resolution document uploaded and OCR processed. |
| **Case Resolved** | `case_resolved` | Final sanction or acquittal logged; case closed. |

### State Machine Transition Rules

State transitions are governed by [`App\Enums\CaseStatus`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Enums/CaseStatus.php):

```php
public function validTransitions(): array
{
    return match ($this) {
        self::UNDER_INFORMAL_DISCUSSION => [self::UNDER_FORMAL_INVESTIGATION],
        self::UNDER_FORMAL_INVESTIGATION => [self::RESOLUTION_DRAFTED],
        self::RESOLUTION_DRAFTED => [self::CASE_RESOLVED],
        self::CASE_RESOLVED => [],
    };
}
```

The [`TribunalCase::transitionTo()`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Models/TribunalCase.php#L28-L35) model method blocks invalid state jumps, guaranteeing audit compliance.

---

## 4. Key Implementation Files

* **Middleware:** [`app/Http/Middleware/EnsureTribunalAccess.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Middleware/EnsureTribunalAccess.php)
* **Controller (API Upload):** [`app/Http/Controllers/TribunalDocumentController.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Controllers/TribunalDocumentController.php)
* **Controller (Web Views):** [`app/Http/Controllers/TribunalController.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Http/Controllers/TribunalController.php)
* **Queue Job:** [`app/Jobs/ProcessDocumentOcr.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Jobs/ProcessDocumentOcr.php)
* **OCR Python Script:** [`scripts/ocr_processor.py`](file:///c:/MyXampp/htdocs/student-conduct-management-system/scripts/ocr_processor.py)
* **Status Enum:** [`app/Enums/CaseStatus.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Enums/CaseStatus.php)
* **Eloquent Model:** [`app/Models/TribunalCase.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/app/Models/TribunalCase.php)
* **Review Blade View:** [`resources/views/tribunal/cases/show.blade.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/resources/views/tribunal/cases/show.blade.php)
* **Routes File:** [`routes/web.php`](file:///c:/MyXampp/htdocs/student-conduct-management-system/routes/web.php)

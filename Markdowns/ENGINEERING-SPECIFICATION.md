# Engineering Specification: Student Disciplinary Management System (SDMS)

## 1. Executive Summary

Build a comprehensive Student Disciplinary Management System (SDMS) to automate account management, offense documentation, and real-time student standing evaluation for CSU (Cavite State University). The system must enforce data integrity through soft-delete mechanisms, provide role-based access control, and maintain audit trails for compliance and appeals.

**System Owner:** Dean of Student Affairs  
**Primary Users:** Administrators, Tribunal Staff, Students  
**Timeline:** [To be defined]  
**Budget:** [To be defined]

---

## 2. Problem Statement

**Current State:**
- Manual account management prone to inconsistencies
- Paper-based or unstructured offense reporting creates bottlenecks
- Student standing is manually updated, causing delays and errors
- No audit trail for disciplinary decisions
- Inability to hold clearances automatically based on sanctions

**Desired State:**
- Centralized, role-based account management with automated provisioning
- Structured, evidence-tracked offense filing with real-time status updates
- Dynamic student standing computed in real-time based on offense data
- Automatic clearance holds triggered by probation/sanction status
- Complete audit trail for regulatory compliance

---

## 3. Functional Requirements

### 3.1 User Account Management (Full CRUD)

#### Create (C)
- **Endpoint:** `POST /api/v1/users`
- **Actors:** System Admin, Authorized Staff
- **Input Validation:**
  - Email format validation (CSU institutional email preferred)
  - Phone number format (Philippine format)
  - Unique constraint checks (email, ID number)
  - Role assignment from predefined enum: `ADMIN`, `STAFF_TRIBUNAL`, `STUDENT`
  - Password complexity: min 12 characters, uppercase, lowercase, number, special character
  - Onboarding data: Full name, ID number, contact info, department/course
- **Success Response:** `201 Created` with user object and system-generated user ID
- **Audit:** Log account creation with actor, timestamp, and changes

#### Read (R)
- **Endpoint:** `GET /api/v1/users` (list), `GET /api/v1/users/{id}` (single)
- **Features Required:**
  - Dynamic data grid with real-time filtering (by name, ID, email, role, status)
  - Server-side pagination (configurable page size, default 50)
  - Advanced profiling window showing:
    - Account metadata (created date, last login, status)
    - Active cases count
    - Current standing status
    - Contact history
  - Role-based visibility (students see limited profiles; admins see all data)
- **Response:** User object with standing status computed in real-time

#### Update (U)
- **Endpoint:** `PATCH /api/v1/users/{id}`
- **Editable Fields:**
  - Role assignment (with audit logging)
  - Contact information (phone, emergency contact)
  - Account status: `ACTIVE`, `SUSPENDED`, `INACTIVE`
  - Department/course updates
- **UI:** Modal or dedicated form with change summary and confirmation
- **Audit:** Log all mutations with before/after values
- **Validation:** Prevent role demotion of users without explicit audit trail

#### Delete (D)
- **Endpoint:** `DELETE /api/v1/users/{id}` (soft-delete)
- **Mechanism:** Set `is_active = false` and `deleted_at = NOW()`
- **Preserve:** All relationships, offense history, transaction logs
- **Visibility:** Soft-deleted users excluded from default queries
- **Recovery:** Admin-only restore endpoint `POST /api/v1/users/{id}/restore`
- **Audit:** Log deletion actor, reason, and timestamp

---

### 3.2 Filing of Offense Report (Transaction Module)

#### Create Offense Report
- **Endpoint:** `POST /api/v1/offenses`
- **Actors:** Staff (Tribunal), Admin
- **Required Fields:**
  - `student_id` (UUID, must exist)
  - `offense_type` (enum: `MINOR`, `MAJOR`, `CRITICAL`)
  - `offense_category` (enum: `ACADEMIC_DISHONESTY`, `CONDUCT_VIOLATION`, `ATTENDANCE`, `PROPERTY_DAMAGE`, `OTHER`)
  - `incident_date` (ISO 8601 timestamp, cannot be future-dated)
  - `incident_time` (HH:MM format, 24-hour)
  - `location` (string, required)
  - `description` (text, min 50 characters, max 5000)
  - `witnesses` (array of names/IDs, optional)
  - `digital_evidence` (file array)

#### Digital Evidence Handling
- **Supported Formats:** `.pdf`, `.jpg`, `.jpeg`, `.png`, `.docx`, `.xlsx`
- **Max File Size:** 10 MB per file, 50 MB total per offense
- **Storage:** Encrypted cloud storage (AWS S3 or equivalent)
- **Retrieval:** Signed URLs with time-limited access (24-hour expiry)
- **Validation:** Virus scan all uploads (e.g., ClamAV)
- **Audit:** Log file access, downloads, with timestamp and actor

#### Offense Status Workflow
- **Draft:** `DRAFT` (auto-saved, not visible to students)
- **Filed:** `SUBMITTED` (timestamp locked, available for tribunal review)
- **Under Review:** `UNDER_INVESTIGATION` (tribunal assigned)
- **Resolved:** `RESOLVED` (final status, tribunal verdict applied)
- **Appealed:** `APPEALED` (reopened, awaiting appeal decision)

#### Response
- `201 Created` with offense ID, filing timestamp, and assigned case number
- Student notified via email upon status changes

---

### 3.3 Dynamic Student Standing Engine (Real-Time Evaluation)

#### Standing Classification Logic

**Good Standing (`GOOD`)**
- No active/unresolved offenses
- All minor offenses cleared (>6 months elapsed without recurrence)
- Zero pending sanctions or community service hours remaining

**Under Review / Pending (`REVIEW`)**
- 1+ active offenses in `UNDER_INVESTIGATION` or `APPEALED` status
- Tribunal decision pending
- Cannot graduate or change academic standing until resolved

**Probation / Sanctioned (`PROBATION`)**
- 1+ major offenses with `RESOLVED` status
- Active sanctions (community service hours, fine, enrollment restriction)
- Remaining hours > 0 or fine unpaid
- **Automatic Clearance Block:** System prevents clearance issuance until sanctions completed

**Suspended (`SUSPENDED`)**
- 2+ major offenses or 1+ critical offense
- Tribunal-ordered suspension active
- Academic registration blocked
- Clearance system blocks all requests

**Expelled (`EXPELLED`)**
- Critical offense with permanent sanction
- Account flagged, all clearance requests denied permanently

#### Real-Time Evaluation Mechanism

**Trigger Points:**
- New offense filed → evaluate standing immediately
- Offense status changes → recompute standing
- Scheduled nightly batch job (11 PM) for consistency audit

**Implementation Approach:**
```
Option A: Database Triggers (PostgreSQL)
- After INSERT/UPDATE on offenses table
- Call stored procedure `sp_evaluate_student_standing(student_id)`
- Update students.standing_status and students.standing_updated_at

Option B: Event-Driven Backend (Recommended)
- Kafka/RabbitMQ event published on offense mutation
- Standing Service consumer processes event asynchronously
- Computes standing, publishes StudentStandingChanged event
- Updates cache (Redis) for instant Read queries
```

**Performance Requirements:**
- Standing query response: <100ms (via cached materialized view)
- Full recompute job: <5 seconds per student
- No N+1 queries; use JOINs and aggregations

#### Clearance System Integration

**Clearance Holds:**
- Query endpoint `GET /api/v1/students/{id}/clearance-hold`
- Returns hold reason if standing in `PROBATION`, `SUSPENDED`, or `EXPELLED`
- Registrar integration via webhook to block diploma processing
- Override capability: Dean-only permission with audit logging

---

## 4. Technical Specifications

### 4.1 Architecture Overview

```
Frontend (React/Vue)
    ↓
API Gateway (Kong / AWS API Gateway)
    ↓
Backend Services:
  ├─ Account Service (User CRUD)
  ├─ Offense Service (Filing, storage)
  ├─ Standing Service (Real-time evaluation)
    ↓
Database (PostgreSQL)
    ├─ users
    ├─ offenses
    ├─ standing_history (audit)
    ├─ digital_evidence
    ├─ appeal_records
    ↓
File Storage (AWS S3 / MinIO)
Cache Layer (Redis)
Message Queue (Kafka / RabbitMQ)
Audit Log (immutable, separate schema)
```

### 4.2 Database Schema (Core Tables)

**users**
```sql
id (UUID, PK)
email (VARCHAR, UNIQUE)
first_name, last_name
student_id / staff_id (VARCHAR, UNIQUE)
role (ENUM: ADMIN, STAFF_TRIBUNAL, STUDENT)
password_hash (bcrypt)
status (ENUM: ACTIVE, SUSPENDED, INACTIVE)
created_at, updated_at, deleted_at
is_active (BOOLEAN, soft-delete flag)
```

**offenses**
```sql
id (UUID, PK)
student_id (UUID, FK → users.id)
filed_by (UUID, FK → users.id)
offense_type (ENUM: MINOR, MAJOR, CRITICAL)
offense_category (VARCHAR)
incident_date, incident_time
location (VARCHAR)
description (TEXT)
status (ENUM: DRAFT, SUBMITTED, UNDER_INVESTIGATION, RESOLVED, APPEALED)
tribunal_assigned_to (UUID, FK → users.id, nullable)
tribunal_decision (TEXT, nullable)
resolution_date (TIMESTAMP, nullable)
sanction_details (JSONB: community_service_hours, fine, restrictions)
created_at, updated_at
case_number (VARCHAR, UNIQUE, auto-generated)
```

**students_standing**
```sql
id (UUID, PK)
student_id (UUID, FK → users.id, UNIQUE)
standing_status (ENUM: GOOD, REVIEW, PROBATION, SUSPENDED, EXPELLED)
active_cases_count (INT)
major_offenses_count (INT)
pending_sanctions (JSONB)
standing_computed_at (TIMESTAMP)
notes (TEXT)
created_at, updated_at
```

**digital_evidence**
```sql
id (UUID, PK)
offense_id (UUID, FK → offenses.id)
file_name (VARCHAR)
file_type (VARCHAR)
s3_key (VARCHAR, encrypted)
file_size (INT)
virus_scan_status (ENUM: PENDING, CLEAN, FLAGGED)
uploaded_by (UUID, FK → users.id)
uploaded_at (TIMESTAMP)
deleted_at (TIMESTAMP, soft-delete)
```

**audit_logs** (immutable, append-only)
```sql
id (UUID, PK)
entity_type (VARCHAR: users, offenses, standing)
entity_id (UUID)
action (VARCHAR: CREATE, UPDATE, DELETE)
actor_id (UUID, FK → users.id)
changes (JSONB: {field: old_value, new_value})
timestamp (TIMESTAMP)
ip_address (VARCHAR)
user_agent (VARCHAR)
```

---

### 4.3 API Endpoints (RESTful)

#### User Management
```
POST   /api/v1/users                    Create user
GET    /api/v1/users                    List users (filtered, paginated)
GET    /api/v1/users/{id}               Fetch single user
PATCH  /api/v1/users/{id}               Update user
DELETE /api/v1/users/{id}               Soft-delete user
POST   /api/v1/users/{id}/restore       Restore soft-deleted user (ADMIN only)
POST   /api/v1/users/{id}/suspend       Suspend account (ADMIN only)
```

#### Offense Management
```
POST   /api/v1/offenses                 File new offense
GET    /api/v1/offenses                 List offenses (filtered, paginated)
GET    /api/v1/offenses/{id}            Fetch offense detail
PATCH  /api/v1/offenses/{id}            Update offense (status, decision)
GET    /api/v1/offenses/{id}/evidence   List evidence files
POST   /api/v1/offenses/{id}/evidence   Upload evidence
DELETE /api/v1/offenses/{id}/evidence/{file_id}  Delete evidence
POST   /api/v1/offenses/{id}/assign     Assign to tribunal member
POST   /api/v1/offenses/{id}/resolve    Finalize offense (record decision)
POST   /api/v1/offenses/{id}/appeal     Student initiates appeal
```

#### Standing & Clearance
```
GET    /api/v1/students/{id}/standing          Fetch current standing
GET    /api/v1/students/{id}/standing-history  Audit trail
GET    /api/v1/students/{id}/clearance-hold    Check clearance eligibility
POST   /api/v1/students/{id}/clearance-hold/override  Dean override (audit logged)
GET    /api/v1/reports/standing-summary        Admin dashboard (standing distribution)
```

---

### 4.4 Security & Compliance

**Authentication:**
- JWT with RS256 (asymmetric signing)
- Token expiry: 1 hour; refresh token: 7 days
- MFA required for ADMIN and STAFF_TRIBUNAL roles (TOTP or SMS)

**Authorization:**
- Role-based access control (RBAC) enforced at API gateway
- Field-level authorization (students cannot view sensitive tribunal notes)
- Principle of least privilege for data access

**Encryption:**
- TLS 1.2+ for all transport
- AES-256 for data at rest
- Sensitive fields encrypted: passwords, evidence file paths, personal IDs
- Key rotation every 90 days

**Audit & Compliance:**
- Immutable audit log for all mutations (cannot be deleted, only appended)
- GDPR-compliant data retention policy (7 years for disciplinary records)
- Right-to-access: students can request their full offense/standing history
- Right-to-be-forgotten: personal data deleted after graduation + 5 years (configurable)

**Data Validation:**
- Input sanitization (XSS prevention, SQL injection prevention via parameterized queries)
- File upload validation (MIME type, magic bytes, size limits)
- No personally identifiable information (PII) in error messages

---

## 5. Non-Functional Requirements

| Requirement | Target | Notes |
|---|---|---|
| **Availability** | 99.5% uptime | Scheduled maintenance windows allowed |
| **Response Time** | <500ms (p95) | Read operations <100ms, writes <500ms |
| **Scalability** | Support 10,000+ concurrent users | Horizontal scaling via load balancer |
| **Data Retention** | 10 years (disciplinary records) | Encrypted, cold storage after 3 years |
| **Recovery Time Objective (RTO)** | 1 hour | Database backup + restore |
| **Recovery Point Objective (RPO)** | 15 minutes | Continuous replication |
| **Accessibility** | WCAG 2.1 AA compliance | Screen reader support, keyboard navigation |

---

## 6. Constraints & Assumptions

**Constraints:**
- Budget: [TBD]
- Team size: [TBD]
- No changes to CSU's Active Directory integration (LDAP)
- Deployment must run on CSU infrastructure (on-premises or hybrid cloud)
- Compliance: Local data privacy laws (PH Data Privacy Act)

**Assumptions:**
- Students have valid CSU email addresses
- Tribunal members trained on system before launch
- Digital evidence preservation requires 10+ years of storage
- Appeals process requires <10 business day turnaround

---

## 7. Success Criteria & Acceptance Tests

**Functional Acceptance:**
- ✅ All user CRUD operations work with role-based restrictions
- ✅ Offense filing captures all required fields and evidence
- ✅ Student standing recalculates within 5 seconds of offense status change
- ✅ Clearance hold blocks registration for PROBATION/SUSPENDED students
- ✅ Soft-deleted users recoverable by ADMIN role only
- ✅ Audit log captures 100% of data mutations with actor and timestamp

**Performance Acceptance:**
- ✅ Standing query response <100ms (p95)
- ✅ User list endpoint <200ms with 1000 records (p95)
- ✅ Bulk offense import (1000 records) completes <30 seconds

**Security Acceptance:**
- ✅ No SQL injection vulnerability (automated scanning)
- ✅ No XSS vulnerability (automated scanning)
- ✅ Passwords meet NIST complexity requirements
- ✅ MFA enforced for privileged roles
- ✅ All PII encrypted at rest and in transit

**Compliance Acceptance:**
- ✅ Audit log passes immutability verification
- ✅ Data retention policy enforced (auto-archival at 3 years)
- ✅ Right-to-access export generates in <5 minutes
- ✅ WCAG 2.1 AA automated accessibility scan passes

---

## 8. Dependencies & Risks

**External Dependencies:**
- CSU LDAP/Active Directory for staff provisioning
- Email service (SMTP) for notifications
- Cloud storage provider (AWS S3 or on-premises MinIO)

**Technical Risks:**
| Risk | Mitigation |
|---|---|
| Standing computation delays during peak load | Implement caching; async job queue for recomputes |
| Data loss from soft-delete | Regular backups (daily), point-in-time recovery tests (monthly) |
| Unauthorized access to offense evidence | File-level encryption, signed URLs with expiry |
| Appeal process creating standing loops | Explicit state machine validation, tribunal lock on appeal |

**Organizational Risks:**
- Tribunal staff resistance to system (mitigation: training + change management)
- Student appeals overwhelming tribunal workflow (mitigation: SLA definition + escalation)

---

## 9. Deliverables & Timeline

**Phase 1: Foundation (Weeks 1-4)**
- Database schema finalized and reviewed
- API skeleton with authentication
- User CRUD endpoints (no offense logic)
- Deployment pipeline (CI/CD)

**Phase 2: Offense Management (Weeks 5-8)**
- Offense filing endpoints
- Digital evidence storage integration
- Email notifications
- Audit logging

**Phase 3: Standing Engine (Weeks 9-12)**
- Standing classification logic
- Real-time evaluation triggers
- Clearance hold integration
- Dashboard reporting

**Phase 4: Testing & Launch (Weeks 13-16)**
- Security testing (pen test, static analysis)
- Load testing
- UAT with tribunal staff
- Data migration (if applicable)
- Production launch

---

## 10. Glossary

| Term | Definition |
|---|---|
| **Offense** | Documented violation of student conduct code with evidence and tribunal review |
| **Standing** | Real-time classification of student's disciplinary status (Good, Review, Probation, Suspended, Expelled) |
| **Tribunal** | Authorized disciplinary body reviewing offenses and issuing decisions |
| **Sanction** | Penalty or restriction imposed following offense resolution (community service, fine, suspension) |
| **Clearance** | Academic/administrative certification required before graduation or enrollment changes |
| **Soft-Delete** | Marking record as inactive without physically removing from database |
| **Audit Trail** | Immutable log of all system actions with actor, timestamp, and changes |

---

## 11. Sign-Off

**Prepared By:** [Engineering Lead]  
**Reviewed By:** [Product Manager, Compliance Officer]  
**Approved By:** [Department Head]  
**Date:** [Date]  
**Version:** 1.0

---

## Appendix A: UI/UX Wireframe Locations

- [Offense Filing Form]  
- [Standing Dashboard]  
- [User Management Grid]  
- [Evidence Viewer]

## Appendix B: Sample Data Model Diagram

[ER Diagram to be inserted]

## Appendix C: API Rate Limiting & Quotas

- Public endpoints: 100 req/min per IP
- Authenticated endpoints: 1000 req/min per user
- Bulk operations: 10 req/min per user

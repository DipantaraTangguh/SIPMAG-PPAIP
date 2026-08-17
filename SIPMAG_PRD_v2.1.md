# PRODUCT REQUIREMENTS DOCUMENT

**Sistem Informasi Portal Magang (SIPMAG)**

Universitas Bakrie — UPT PPAIP

| Field       | Value                              |
|-------------|------------------------------------|
| Project Name | Sistem Informasi Portal Magang (SIPMAG) |
| Version     | 2.1 (Revised — Form 2 Mandiri Update) |
| Status      | Ready for Implementation           |
| Institution | Universitas Bakrie                 |
| Unit        | UPT PPAIP                          |
| Platform    | Web-based (React.js + Laravel 12)  |
| Date        | April 2026                         |

---

# 1. Executive Summary

## 1.1 Objective

To develop a web-based platform that centralizes the entire internship administration and monitoring process under UPT PPAIP at Universitas Bakrie. The system digitalizes prerequisite document verification (Form 1 & Form 2), facilitates internship applications via two tracks (Portal and Mandiri), manages DPM assignment, tracks logbook-based guidance sessions, and controls the full internship lifecycle through to final sidang submission.

## 1.2 Problem Statement

- Manual verification of prerequisite documents is time-consuming and error-prone.
- No centralized monitoring of student internship progress (guidance sessions, logbook).
- No automated prevention of duplicate or conflicting internship applications.
- Multiple study programs managed by different Kaprodi with no unified system.
- No structured tracking of who has or has not completed internship requirements.
- No system record of which companies Mandiri-track students are formally approaching.

## 1.3 System Goals

- Centralize student, vacancy, supervisor, and document data in a single database.
- Automate business rule validation (mutual exclusion, logbook gate, auto-cancel).
- Provide role-based and study-program-scoped access control for 4 user roles.
- Enable PPAIP to manually finalize each student's internship cycle after offline sidang.
- Record all Form 2 (Surat Pengantar Magang) submissions for Mandiri students so PPAIP has full visibility of company outreach.

---

# 2. Scope of Work

## 2.1 In-Scope

- Form 1 submission, Kaprodi verification, and auto-generated signed PDF.
- **Form 2 submission as a system-generated form (Mandiri track): student fills in company data, PPAIP reviews and approves, system generates signed PDF for student to distribute to company. Student may submit Form 2 multiple times for different companies.**
- Supervisor Application Form and Kaprodi DPM assignment (scoped by study program).
- Logbook module: student submission and DPM approval (6 entries required).
- Sidang submission form with 3 file uploads (Laporan, Poster, KRS).
- PPAIP manual cycle reset per student after offline sidang confirmation.
- Role-based Filament admin panel for PPAIP, Kaprodi, and DPM.
- CV, LoA, and report bulk download (ZIP) for PPAIP.

## 2.2 Out-of-Scope

- Company HR login — selection decisions are communicated externally.
- Email or push notifications — all updates are UI-based only.
- Quota management per vacancy — unlimited applicants allowed.
- Student account self-registration — accounts pre-seeded from campus database.
- Numerical/letter grading of internship performance.
- Digital signature implementation — PDF templates use pre-designed letterhead.
- Linking Form 2 submissions to LoA submissions — LoA upload is independent.

---

# 3. User Roles & Personas

| **Role**  | **Login Method** | **Scope**               | **Primary Responsibility**                                                        |
|-----------|------------------|-------------------------|-----------------------------------------------------------------------------------|
| Mahasiswa | NIM + Password   | Own data only           | Submit forms, apply for internships, fill logbook, submit sidang files.           |
| Kaprodi   | Email + Password | Own study program only  | Approve/reject Form 1 with reason, assign DPM to students.                        |
| PPAIP     | Email + Password | All study programs      | Manage vacancies, approve/reject Form 2, download CVs, finalize sidang cycle reset. |
| DPM       | Email + Password | Assigned students only  | Approve/reject logbook entries, view assigned student progress.                   |

## 3.1 Role Scoping Rules

- PPAIP has full visibility across all study programs — no scoping applied.
- Kaprodi only sees and acts on students whose study_program matches their own.
- DPM only sees students assigned to them via dpm_id.
- Mahasiswa can only view and interact with their own data.

---

# 4. Functional Requirements & Acceptance Criteria

## 4.1 Phase 1 — Form 1 Module (One-Time per Cycle)

*User Story: As a student, I want to submit my academic eligibility form so I can proceed with internship registration.*

| **ID** | **Requirement**                                                       | **Acceptance Criteria**                                                                                                                                                                                                                                                  |
|--------|-----------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| FR-01  | Student submits Form 1 (Surat Keterangan Memenuhi Syarat Akademik).   | Fields: Nama, NIM, Program Studi, Semester/Tahun Akademik, Jumlah SKS, IPK, Rencana Skema Magang, Topik/Tempat Magang, Output yang ditargetkan. Status set to PendingReview on submission.                                                                              |
| FR-02  | Kaprodi reviews Form 1 — Approve or Reject.                           | If Approved: system auto-generates signed Form 1 PDF using pre-designed letterhead (Kaprodi name, NIDN, date auto-filled). Status becomes ApprovedForm1. Download button appears for student. If Rejected: Kaprodi must input rejection reason. Status becomes RejectedForm1. Student sees rejection reason on dashboard and must revise and resubmit. |
| FR-03  | Form 1 is a one-time process per internship cycle.                    | Once ApprovedForm1, student does not re-submit for different applications in the same cycle. Resets only when PPAIP triggers cycle reset.                                                                                                                                 |

---

## 4.2 Phase 2A — Portal Track (Magang Mitra)

*User Story: As a verified student, I want to browse and apply for partner internship vacancies.*

| **ID** | **Requirement**                                            | **Acceptance Criteria**                                                                                                                                                                                                                                         |
|--------|------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| FR-04  | Student browses active vacancy list.                       | Only accessible if access_status = ApprovedForm1 (403 otherwise). Vacancies display: company name, position, vacancy details, job description (bullet list), minimum education, deadline. Supports search by company name and position.                         |
| FR-05  | Student views vacancy detail and applies by uploading CV.  | Detail page shows all 5 fields. Apply button is on detail page only. Student uploads CV (PDF, max 2MB). Application created with status = Applied. Maximum 5 simultaneous Applied applications enforced — returns 422 if limit exceeded.                        |

---

## 4.3 Phase 2B — Mandiri Track (Magang Mandiri)

*User Story: As a verified student who is seeking their own internship placement, I want to generate an official introduction letter (Form 2) for each company I approach, so that UPT PPAIP has a record of my outreach and I have a signed document to submit to the company.*

> **⚠ REVISION v2.1:** Form 2 is no longer a static downloadable template. It is now a system-generated form with PPAIP approval, mirroring the Form 1 flow. Students may submit Form 2 multiple times — once per company they intend to approach. There is no limit on the number of Form 2 submissions per cycle.

| **ID** | **Requirement**                                                              | **Acceptance Criteria**                                                                                                                                                                                                                                                                    |
|--------|------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| FR-06  | Student fills and submits Form 2 (Surat Pengantar Magang) via the system.    | Form 2 is accessible after Form 1 is approved (access_status = ApprovedForm1). Fields: Nama (auto-filled), NIM (auto-filled), Nama Perusahaan, Nama dan Jabatan Contact Person, Nomor/Email Contact Person, Lingkup Magang, Tanggal Mulai Magang, Tanggal Selesai Magang. Status set to PendingReview on submission. |
| FR-07  | PPAIP reviews each Form 2 submission — Approve or Reject.                    | If Approved: system auto-generates signed Form 2 PDF using pre-designed letterhead (PPAIP signatory: Dr. Rizki Maryam Astuti, M.Si., NIDN 030801198505, date auto-filled). Status becomes ApprovedForm2. Download button appears for student. If Rejected: PPAIP must input rejection reason. Status becomes RejectedForm2. Student sees rejection reason and may revise and resubmit. |
| FR-08  | Student can submit Form 2 multiple times for different companies.            | No limit on the number of Form 2 submissions per cycle. Each submission is a separate record in the form2_submissions table linked to the student. Student can track the status of each Form 2 submission (PendingReview, ApprovedForm2, RejectedForm2) in their dashboard.                |
| FR-09  | Student downloads the approved Form 2 PDF and distributes it to the company. | Download button only appears after status = ApprovedForm2. Student distributes the PDF externally to the target company. No upload or return confirmation required. This step completes the Mandiri outreach process within the system.                                                     |

**Note on LoA Independence:** The LoA submission (Phase 3) is completely independent from Form 2 submissions. There is no required link between a specific Form 2 and the LoA upload. Students upload their LoA separately when they have secured an internship placement, regardless of how many Form 2s were submitted.

---

## 4.4 Phase 3 — Supervisor Application Form (Both Tracks)

*User Story: As a student with a confirmed internship placement, I want to formally register my company details and request a supervisor.*

| **ID** | **Requirement**                                  | **Acceptance Criteria**                                                                                                                                                                                                                                                                              |
|--------|--------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| FR-10  | Student submits DPM Application Form.            | Form fields: Nama (auto-filled), NIM (auto-filled), Nama Perusahaan, Kontak Perusahaan, Upload LoA (PDF, max 2MB). Applicable to both Portal and Mandiri students. No approval or rejection step — form enters admin panel immediately upon submission.                                               |
| FR-11  | Kaprodi assigns DPM to student via admin panel.  | Kaprodi sees supervisor application forms from students in their study program only. Kaprodi selects a DPM from the lecturer master data. Assignment is immediate — no approval step. Assigned DPM name and contact appears on student dashboard automatically.                                       |
| FR-12  | Student sees assigned DPM on dashboard.          | After assignment, dashboard shows DPM name and contact. If no DPM yet: shows 'Menunggu Penugasan DPM'.                                                                                                                                                                                               |

---

## 4.5 Phase 4 — Logbook Module (6 Entries Required)

*User Story: As a student, I want to record my daily internship activities and have them approved by my DPM.*

| **ID** | **Requirement**                                            | **Acceptance Criteria**                                                                                                                                                                                                                                          |
|--------|------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| FR-13  | Student submits logbook entries.                           | Each entry contains: Tanggal, Kegiatan Harian, Hasil. Student can submit entries anytime after DPM is assigned. Entry status defaults to PendingReview.                                                                                                         |
| FR-14  | DPM approves or rejects each logbook entry.                | DPM sees all logbook entries from assigned students. Approve: status = Approved, counted toward 6. Reject: status = Rejected, DPM may optionally add a note. Student revises and resubmits.                                                                    |
| FR-15  | Sidang button activates after 6 approved logbook entries.  | 'Ajukan Sidang Magang' button is hidden/disabled until exactly 6 entries have status = Approved. Activates automatically when condition is met. Progress shown as X/6 on dashboard.                                                                              |

---

## 4.6 Phase 5 — Sidang Submission & Completion

*User Story: As a student who completed 6 guidance sessions, I want to submit final documents and proceed to the internship examination.*

| **ID** | **Requirement**                                                           | **Acceptance Criteria**                                                                                                                                                                                                                                                                                                                                           |
|--------|---------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| FR-16  | Student submits Sidang form after 6 approved logbook entries.             | Form uploads: Laporan Magang Akhir (PDF, max 2MB), Poster Presentasi (PDF, max 2MB), KRS proof of internship course (PDF, max 2MB). No approval or rejection — files stored immediately. Status set to AwaitingDefense.                                                                                                                                            |
| FR-17  | Student data remains fully visible during AwaitingDefense.                 | All data (DPM, logbook, company, LoA, files) remains accessible on student dashboard and admin panel while status = AwaitingDefense. No data cleared automatically.                                                                                                                                                                                                |
| FR-18  | PPAIP manually resets internship cycle per student after offline sidang.  | PPAIP sees list of students with status = AwaitingDefense. 'Selesaikan Siklus' button is per individual student — not bulk. Confirmation modal: 'Apakah Anda yakin? Semua data siklus mahasiswa ini akan direset.' On confirm: access_status = Unverified, dpm_id = null, is_independent = false, logbook count = 0. Application/logbook history retained in DB for audit. |

---

# 5. Business Rules & Logic

## 5.1 Access Control Gates

| **Rule**              | **Condition**                                                                                      | **Enforcement**                          |
|-----------------------|----------------------------------------------------------------------------------------------------|------------------------------------------|
| Form 1 Gate           | access_status must be ApprovedForm1 to access Portal, Mandiri Form 2, and Supervisor Form.         | API returns 403 Forbidden if not met.    |
| Form 2 Gate           | Student must have access_status = ApprovedForm1 to submit Form 2 (Mandiri track).                 | API returns 403 Forbidden if not met.    |
| Concurrency Limit     | Maximum 5 simultaneous Applied applications per student (Portal track only).                       | API returns 422 if count >= 5.           |
| Mutual Exclusion      | Mitra and Mandiri tracks cannot be used simultaneously.                                            | 403 on cross-track attempt.              |
| Logbook Gate          | Sidang button only activates after 6 approved logbook entries.                                     | Button disabled/hidden until condition met. |
| DPM Assignment Gate   | DPM assignment only appears for students with submitted Supervisor Form.                            | UI-level enforcement in Filament.        |

## 5.2 Auto-Cancellation Logic

- Trigger: One application status changes to Accepted.
- Action: All other Applied applications for that student are set to Canceled.
- Implementation: Laravel Model Observer on Application model. Wrapped in DB::transaction() with lockForUpdate() to prevent race conditions.
- Single Acceptance Rule: Only one Accepted application per student at any time.

## 5.3 Study Program Scoping

- Kaprodi account has a study_program value. All queries for students are scoped to WHERE students.study_program = kaprodi.study_program.
- PPAIP has no scoping — full visibility across all programs.
- DPM scoped to students WHERE dpm_id = lecturer.id.

## 5.4 Cycle Reset Rules

- Reset is triggered manually by PPAIP per individual student — never bulk, never automatic.
- Fields reset: access_status = Unverified, dpm_id = null, is_independent = false, logbook count = 0.
- Historical records (applications, logbooks, form2_submissions, sidang files) are NOT deleted — retained for audit.
- After reset, student must resubmit Form 1 and complete the full cycle again.

## 5.5 PDF Generation Rules

- Form 1 PDF is auto-generated from a pre-designed letterhead template after Kaprodi approves.
  - Template auto-fills: student name, NIM, program, Kaprodi name, NIDN, approval date.
- **Form 2 PDF is auto-generated from a pre-designed letterhead template after PPAIP approves each Form 2 submission.**
  - **Template auto-fills: student name, NIM, company name, contact person, lingkup magang, periode magang, PPAIP signatory (Dr. Rizki Maryam Astuti, M.Si., NIDN 030801198505), approval date.**
  - **Each approved Form 2 submission generates a separate PDF.**
- All generated/uploaded PDFs stored in Laravel private storage and served via secured routes only.

## 5.6 Form 2 Multiplicity Rules

- A student may submit Form 2 as many times as needed within a single cycle — one per company they intend to approach.
- Each Form 2 submission is an independent record in the form2_submissions table.
- Each submission has its own status lifecycle: PendingReview → ApprovedForm2 / RejectedForm2.
- If rejected, student revises and resubmits — this creates a new submission record, not an overwrite.
- The number of Form 2 submissions has no bearing on the concurrency limit (which only applies to Portal track applications).
- LoA upload (Phase 3) is fully independent — it is not required to reference any specific Form 2 submission.

---

# 6. Non-Functional Requirements

| **Category**    | **Requirement**                                                                                                                                                         |
|-----------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Security        | Laravel Sanctum (Bearer Token) for student API. Session Auth for admin roles (Kaprodi, PPAIP, DPM) in Filament. Laravel Policies for IDOR protection.                  |
| Platform        | Frontend: React.js + Tailwind CSS v4 (Vite). Backend & Admin: Laravel 12 + Filament PHP v3. Database: MySQL 8.                                                         |
| Data Integrity  | DB::transaction() with lockForUpdate() for Auto-Cancel and cycle reset. All batch operations must be atomic.                                                            |
| File Handling   | Max 2MB per upload. PDF only. Stored in storage/app/private/. Served via Storage::response() for inline viewing.                                                        |
| Notifications   | No email notifications. Status updates reflected via UI dashboard on next login.                                                                                        |
| Branding        | Primary: #682828. Accent: #943939. Universitas Bakrie identity. Applied via Tailwind v4 @theme CSS variables.                                                           |
| Deployment      | VPS Ubuntu 22.04 + Nginx + PHP 8.2 FPM + MySQL 8. php artisan storage:link for file serving.                                                                           |
| Version Control | Git with commit per phase. Commit before any major feature change.                                                                                                      |

---

# 7. Data Entities (ERD Reference)

## 7.1 users

| **Column**            | **Type**   | **Attributes**                | **Description**                |
|-----------------------|------------|-------------------------------|--------------------------------|
| id                    | BigInt     | PK, Auto Inc                  |                                |
| email                 | String     | Unique                        | Login credential (admin roles) |
| password              | String     | Hashed                        |                                |
| role                  | Enum       | admin, student, kaprodi, dpm  | RBAC                           |
| created_at, updated_at| Timestamps |                               |                                |

## 7.2 students

| **Column**              | **Type**   | **Attributes**                                                           | **Description**                  |
|-------------------------|------------|--------------------------------------------------------------------------|----------------------------------|
| id                      | BigInt     | PK                                                                       |                                  |
| user_id                 | BigInt     | FK → users                                                               | 1-to-1                           |
| dpm_id                  | BigInt     | FK → lecturers, Nullable                                                 | Assigned DPM                     |
| nim                     | String     | Unique                                                                   | Digits only, from campus DB      |
| name                    | String     |                                                                          |                                  |
| study_program           | String     |                                                                          | Used for Kaprodi scoping         |
| access_status           | Enum       | Unverified, PendingReview, ApprovedForm1, RejectedForm1, AwaitingDefense  | Lifecycle gatekeeper             |
| is_independent          | Boolean    | Default: false                                                           | Mandiri track marker             |
| form1_data              | JSON       | Nullable                                                                 | Stored Form 1 field values       |
| form1_pdf_path          | String     | Nullable                                                                 | Generated Form 1 PDF path        |
| approved_logbook_count  | Integer    | Default: 0                                                               | Counter for approved logbook entries |
| created_at, updated_at  | Timestamps |                                                                          |                                  |

## 7.3 lecturers

| **Column**              | **Type**   | **Attributes**        | **Description**                |
|-------------------------|------------|-----------------------|--------------------------------|
| id                      | BigInt     | PK                    |                                |
| user_id                 | BigInt     | FK → users, Nullable  | For DPM/Kaprodi login          |
| nidn                    | String     | Unique                | National lecturer ID           |
| lecturer_name           | String     |                       |                                |
| contact                 | String     | Nullable              | Email or phone                 |
| study_program           | String     | Nullable              | For Kaprodi scoping            |
| created_at, updated_at  | Timestamps |                       |                                |

## 7.4 internships

| **Column**              | **Type**   | **Attributes**    | **Description**                    |
|-------------------------|------------|-------------------|------------------------------------|
| id                      | BigInt     | PK                |                                    |
| company_name            | String     |                   |                                    |
| position                | String     |                   | Internship title                   |
| description             | Text       |                   | General description                |
| vacancy_details         | Text       | Nullable          | e.g. 2 positions, WFO, 3 months   |
| job_description         | JSON       | Nullable          | Array of bullet strings            |
| deadline                | Date       |                   |                                    |
| is_active               | Boolean    | Default: true     | Controls portal visibility         |
| created_at, updated_at  | Timestamps |                   |                                    |

## 7.5 applications

| **Column**                | **Type**   | **Attributes**                                  | **Description**                    |
|---------------------------|------------|-------------------------------------------------|------------------------------------|
| id                        | BigInt     | PK                                              |                                    |
| student_id                | BigInt     | FK → students                                   |                                    |
| internship_id             | BigInt     | FK → internships, Nullable                      | Null if Mandiri                    |
| independent_company_name  | String     | Nullable                                        | Mandiri track                      |
| cv_file_path              | String     | Nullable                                        | Portal track only                  |
| loa_path                  | String     | Nullable                                        | From Supervisor Form (both tracks) |
| status                    | Enum       | Applied, Accepted, RejectedByCompany, Canceled  | Application lifecycle              |
| created_at, updated_at    | Timestamps |                                                 |                                    |

## 7.6 form2_submissions *(NEW in v2.1)*

> This table replaces the previous static Form 2 template download. Each row represents one Form 2 submission by a Mandiri-track student targeting one specific company.

| **Column**              | **Type**   | **Attributes**                              | **Description**                                     |
|-------------------------|------------|---------------------------------------------|-----------------------------------------------------|
| id                      | BigInt     | PK                                          |                                                     |
| student_id              | BigInt     | FK → students                               | Student who submitted                               |
| company_name            | String     |                                             | Target company name                                 |
| contact_person_name     | String     |                                             | Company contact person's full name and title        |
| contact_person_role     | String     |                                             | Contact person's job title/role                     |
| contact_info            | String     |                                             | Phone number or email of contact person             |
| lingkup_magang          | Text       |                                             | Scope/field of internship                           |
| tanggal_mulai           | Date       |                                             | Proposed internship start date                      |
| tanggal_selesai         | Date       |                                             | Proposed internship end date                        |
| status                  | Enum       | PendingReview, ApprovedForm2, RejectedForm2 | PPAIP review status                                 |
| rejection_reason        | Text       | Nullable                                    | Filled by PPAIP on rejection                        |
| pdf_path                | String     | Nullable                                    | Auto-generated PDF path after ApprovedForm2         |
| submitted_at            | Timestamp  |                                             | Submission timestamp                                |
| created_at, updated_at  | Timestamps |                                             |                                                     |

## 7.7 logbooks

| **Column**              | **Type**   | **Attributes**                     | **Description**          |
|-------------------------|------------|------------------------------------|--------------------------|
| id                      | BigInt     | PK                                 |                          |
| student_id              | BigInt     | FK → students                      |                          |
| tanggal                 | Date       |                                    | Date of activity         |
| kegiatan_harian         | Text       |                                    | Daily activities         |
| hasil                   | Text       |                                    | Outcomes/results         |
| status                  | Enum       | PendingReview, Approved, Rejected  | DPM review status        |
| dpm_note                | String     | Nullable                           | DPM rejection note       |
| created_at, updated_at  | Timestamps |                                    |                          |

## 7.8 sidang_submissions

| **Column**              | **Type**   | **Attributes**         | **Description**           |
|-------------------------|------------|------------------------|---------------------------|
| id                      | BigInt     | PK                     |                           |
| student_id              | BigInt     | FK → students, Unique  | One per student per cycle |
| laporan_path            | String     |                        | Final report PDF          |
| poster_path             | String     |                        | Presentation poster PDF   |
| krs_path                | String     |                        | KRS proof PDF             |
| submitted_at            | Timestamp  |                        |                           |
| created_at, updated_at  | Timestamps |                        |                           |

## 7.9 Relationships Summary

| **Relationship**                     | **Cardinality** | **Description**                                          |
|--------------------------------------|-----------------|----------------------------------------------------------|
| users → students                     | 1:1             | Each student user has one student profile                |
| users → lecturers                    | 1:1             | Each lecturer/Kaprodi/DPM has one user account           |
| students → applications              | 1:N             | Student can have multiple applications                   |
| students → form2_submissions         | 1:N             | Student can have multiple Form 2 submissions             |
| students → logbooks                  | 1:N             | Student can have multiple logbook entries                |
| students → sidang_submissions        | 1:1             | One sidang submission per cycle                          |
| students → lecturers (dpm)           | N:1 Nullable    | Student is supervised by one DPM                         |
| internships → applications           | 1:N Nullable    | Vacancy receives multiple applications; null for Mandiri |
| lecturers → students (scoping)       | 1:N             | Kaprodi manages students in their study program          |

---

# 8. User Journey Summary

| **Step** | **Actor**            | **Action**                                                                          | **System Response**                                                                                          |
|----------|----------------------|-------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------|
| 1        | Mahasiswa            | Login with NIM + Password                                                           | Dashboard shown with current access_status.                                                                  |
| 2        | Mahasiswa            | Fill and submit Form 1                                                              | Status set to PendingReview.                                                                                 |
| 3        | Kaprodi              | Review Form 1 in admin panel (scoped to own study program)                          | ACC: PDF generated, status = ApprovedForm1. Reject: reason stored, student notified via dashboard.           |
| 4A       | Mahasiswa (Mitra)    | Browse vacancies, open detail page, upload CV, apply                                | Application created, status = Applied. Slot counted toward max 5.                                           |
| 4B       | Mahasiswa (Mandiri)  | Fill and submit Form 2 in system for each target company                            | Form 2 record created, status = PendingReview. PPAIP reviews and approves/rejects each submission.          |
| 4B-ii    | PPAIP                | Review Form 2 submission(s) in admin panel                                          | ACC: Form 2 PDF auto-generated, student can download and distribute. Reject: reason stored, student revises. |
| 5        | PPAIP                | Download CVs as ZIP (Portal track), forward to company                              | ZIP generated per vacancy. External company decision process.                                                |
| 6        | Mahasiswa            | Submit Supervisor Application Form (company name, contact, LoA)                     | Form stored in admin panel. Kaprodi sees it scoped to their program. LoA is independent of Form 2.          |
| 7        | Kaprodi              | Assign DPM to student via admin panel                                               | dpm_id set on student. DPM name appears on student dashboard.                                                |
| 8        | Mahasiswa            | Submit logbook entries (6 required)                                                 | Each entry reviewed by DPM. Counter increments on each approval.                                             |
| 9        | DPM                  | Approve or reject each logbook entry                                                | Approved: counter +1. Rejected: note added, student revises.                                                 |
| 10       | Mahasiswa            | Submit Sidang form (Laporan, Poster, KRS) after 6 approved entries                  | Status = AwaitingDefense. All data remains visible.                                                           |
| 11       | PPAIP                | Click 'Selesaikan Siklus' after confirming offline sidang                           | Confirmation modal → cycle reset to Unverified. History retained.                                            |

---

# 9. Success Metrics

| **Metric**               | **Target**                                           | **Measurement Method**                                                      |
|--------------------------|------------------------------------------------------|-----------------------------------------------------------------------------|
| Verification Efficiency  | Reduction in admin time to process Form 1 vs manual  | Compare avg processing time before and after deployment                     |
| Form 2 Data Coverage     | 100% of Mandiri students have Form 2 records         | students WHERE is_independent = true WITH form2_submissions count > 0       |
| No Double Booking        | 0% students with duplicate Accepted status           | Auto-Cancel logic enforced — verifiable via DB query                        |
| Cycle Completion Rate    | % students reaching AwaitingDefense                   | students WHERE status = AwaitingDefense / total registered                   |
| Logbook Compliance       | % students completing 6 approved logbook entries     | students WHERE approved_logbook_count = 6 / active students                 |
| DPM Assignment Rate      | % students with LoA who have been assigned a DPM     | students WHERE dpm_id IS NOT NULL / students with supervisor form            |

---

# 10. Appendix — Form Field Reference

## Form 1 — Surat Keterangan Memenuhi Syarat Akademik

Student-input fields:
- Nama (auto-filled from student record)
- NIM (auto-filled from student record)
- Program Studi (auto-filled from student record)
- Semester / Tahun Akademik
- Jumlah SKS yang telah diselesaikan
- IPK
- Rencana Skema Magang (Mitra / Mandiri / Kewirausahaan)
- Topik / Tempat Magang (optional — required for Kewirausahaan)
- Output yang ditargetkan (Produk / Prototype)

Auto-filled on PDF generation:
- Tanggal Pengajuan
- Nama Ketua Program Studi & NIDN (from Kaprodi account)
- Approval date and signature block from letterhead template

---

## Form 2 — Surat Pengantar Magang *(REVISED in v2.1)*

> Form 2 is now a system-generated form submitted by Mandiri-track students. Each submission targets one specific company. PPAIP approves each submission and the system generates a signed PDF.

Student-input fields:
- Nama (auto-filled from student record)
- NIM (auto-filled from student record)
- Nama Perusahaan / Instansi
- Nama Contact Person (full name and title)
- Jabatan Contact Person
- Nomor Telepon / Email Contact Person
- Lingkup Magang (scope/field of internship)
- Tanggal Mulai Magang
- Tanggal Selesai Magang

Auto-filled on PDF generation:
- Institution header: Universitas Bakrie, UPT PPAIP
- Signatory block: Dr. Rizki Maryam Astuti, M.Si. — NIDN 030801198505
- Approval date (from PPAIP approval timestamp)
- Student Nama and NIM (from student record)

~~Previously: Form 2 was a static downloadable PDF template. Students filled it manually outside the system and submitted it directly to companies with no system record. This approach has been replaced in v2.1.~~

---

## Supervisor Application Form Fields

- Nama (auto-filled)
- NIM (auto-filled)
- Nama Perusahaan
- Kontak Perusahaan (contact person name, phone/email)
- Upload LoA — Letter of Acceptance from company (PDF, max 2MB)

> Note: LoA upload is independent from Form 2 submissions. There is no required link between a specific Form 2 record and the LoA uploaded here.

---

## Logbook Entry Fields

- Tanggal (Date of activity)
- Kegiatan Harian (Daily activities description)
- Hasil (Outcomes / results)

---

## Sidang Submission Form Fields

- Upload Laporan Magang Akhir (PDF, max 2MB)
- Upload Poster Presentasi (PDF, max 2MB)
- Upload KRS sebagai bukti mata kuliah magang (PDF, max 2MB)

---

*— End of Document — SIPMAG PRD v2.1*

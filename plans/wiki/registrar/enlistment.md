# Registrar Guide — Enlistment

Audience: Registrar staff and admins  
Access: #/registrar/enlistment (roles: registrar, admin)

Purpose
- Manage a student’s enlistment for a specific Academic Term: Add, Drop, Change Section
- Generate/refresh Graduation Checklist (if missing), and auto‑queue classes from checklist
- Preview and save Tuition snapshot for the registration
- Generate Registration Form (PDF) when enlisted/enrolled
- Reset Registration for a term (dangerous action – removes enlisted classes and registration)

Pre‑requisites
- You are logged in with Registrar privileges
- Ensure a global Term is selected from the term selector (top left sidebar)
- Student exists (know the student number)

Key Concepts
- Enlisted vs Enrolled: Enlisted means subjects have been added; Enrolled implies status set by policy/process
- Checklist: Computed from curriculum, used to suggest/auto‑queue planned required subjects
- Classlist: A scheduled section for a subject (has Section Code, remaining slots, etc.)
- Registration: Metadata per term (year level, student type, program/curriculum, tuition year, payment type, LOA remarks, withdrawal period)

Navigation
- Go to Registrar → Enlistment or open #/registrar/enlistment
- Required Roles: registrar, admin

1) Select Student and Term
- Student: Start typing the student number or name; select from autocomplete
- Term: Uses the global term selector; if not set, click the term selector in the sidebar
- Year Level: Numeric year level to use for enlistment
- Student Type: continuing, new, returnee, transfer, shiftee, acadres
- Optional: Block Section filter restricts auto‑queue selections to a specific section code

Tips
- Use View Records to open the student’s records for the selected term (opens #/students/:id/records?sn=xxx)

2) View Current Enlisted (Selected Term)
- After selecting a student and term, click “Current Enlisted” to refresh
- Table shows current enlisted classes for the selected term:
  - Code, Description, Section, Units, Classlist ID
- If none, the list will be empty

3) Queue Operations
Queue operations are staged first, then submitted in a batch.

Add
- Choose a target Classlist from the dropdown (it displays subject code — section — remaining slots)
- The system checks remaining slots; full sections cannot be queued
- With a student selected, prerequisites are checked automatically:
  - If missing prerequisites, you can override and queue anyway
- Click “Queue Add” to stage the add

Drop
- Choose a currently enlisted class to drop and click “Queue Drop”

Change Section
- Choose From (current classlist) and To (target classlist)
- The system checks remaining slots
- Click “Queue Change” to stage the change

Auto‑Queue from Checklist
- Requires a generated checklist
- Filters by Year Level and (optionally) Semester (vm.clYearLevel, vm.clSemInt)
- Runs batch prerequisite checks for candidate subjects
- Queues all viable adds (skips those failing prerequisites unless overridden)

Generate Checklist
- If the student has no checklist, click “Generate Checklist”
- After generation, you may use Auto‑Queue

Pending Operations
- All staged operations are listed (type, details, prerequisite info)
- Remove individual items or Clear all

Submit
- Click “Submit” to process the staged operations
- The system returns a Results summary and a per‑operation status list
- Current Enlisted is then refreshed

4) Registration Details (Right Panel)
- Displays current registration metadata:
  - Year Level, Student Type, Payment Type, Program, Curriculum, Tuition Year, Withdrawal Period, LOA Remarks
- Edit Form: update values and click “Save Registration”
  - Useful when a student’s Program/Curriculum or Tuition Year must be set before tuition preview/save
- Reset resets only the edit form, not the registration record

5) Registration Form (PDF)
- Available when registration is enlisted/enrolled or has a date_enlisted
- Click “Generate Reg Form (PDF)” to download/open the PDF
- If pop‑ups are blocked, the system falls back to a forced download

6) Tuition Details
Load Tuition
- Click “Load Tuition” to compute a tuition preview based on enlisted subjects
- Requires a Program selection and current enlisted subjects
- Dynamic installment plans: when provided, plan tabs show computed DP, per‑installment fee, and total installment

Save Tuition
- After previewing, click “Save Tuition” to persist a tuition snapshot for the registration
- Preflight checks if a snapshot exists; you can overwrite it
- Saved tuition is used for downstream finance processes

Installment Tabs
- If dynamic plans exist (from backend), tabs are built from those
- Legacy fallback tabs (Standard/30% DP/50% DP) show totals without altering backend data

7) Reset Registration
Dangerous operation – requires password confirmation
- Removes enlisted classes and the registration for the selected term
- Provides a success message with deleted counts

8) Troubleshooting
- Term not set: Use the global term selector (top left sidebar)
- No sections found: Ensure classlists exist for the selected term (Registrar → Slot Monitoring)
- Prerequisites unexpected: Retry or coordinate with Academics/Curriculum for correct prerequisite mapping
- Reg Form not available: Ensure status enlisted/enrolled or date_enlisted is present; verify via Registration Details
- Tuition preview empty: Ensure enlisted subjects and program are set

Appendix: Data Integrity and Audit
- All enlistment mutations are audited via the back end’s System Log Service
- Enlistment API supports add, drop, change_section, and registration upsert behavior per student+term

Glossary
- Checklist: Graduation plan based on curriculum
- Classlist: Scheduled section of a subject
- Registration: Per‑term student record (status, program/curriculum, tuition year, etc.)
- Enlisted: Subjects queued/added for the term
- Enrolled: Status per policy indicating confirmed enrollment

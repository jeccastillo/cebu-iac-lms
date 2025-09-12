# TODO — Transcripts/Copy of Grades with Billing

This checklist tracks the implementation steps per plans/implementation_plan_transcripts.md.

task_progress Items:
- [ ] Step 1: Add/verify backend routes for transcript fee, history, and reprint (role:registrar,admin)
- [ ] Step 2: Implement ReportsController::transcriptFee, ::listTranscriptRequests, ::reprintTranscript
- [ ] Step 3: Update ReportsController::studentTranscriptPdf to save TranscriptRequest and create StudentBilling row
- [ ] Step 4: Lint and smoke test backend (php -l, test fee and reprint endpoints)
- [ ] Step 5: Implement frontend ReportsService methods: transcriptFee, listTranscriptRequests, reprintTranscript
- [ ] Step 6: Update RegistrarTranscriptsController to confirm fee (window.confirm), generate, load history, and reprint
- [ ] Step 7: Update transcripts.html to display history and reprint actions
- [ ] Step 8: End-to-end QA: generate transcript/copy with/without fee configured; verify billing row, history, and reprint

Notes:
- Use native window.confirm for billing confirmation (no SweetAlert).
- Create a tb_mas_student_billing row with amount 0.00 when fee not configured; include remark.
- History lists saved transcript_requests; reprint streams the PDF inline.

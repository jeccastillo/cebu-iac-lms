# Grading Sheet PDF - Logo Not Displaying (Fix Plan & Checklist)

Context:
- PDF is rendered by App\Services\Pdf\GradingSheetPdf using FPDI.
- The logo is drawn only if $dto['logo_path'] points to an existing file.
- DTO is built by App\Services\GradingSheetService::buildDto() which sets 'logo_path' via detectLogoPath().
- In the mono-repo, the actual file exists at project-root/assets/img/iacademy-logo.png (outside laravel-api).
- Original detectLogoPath() only checked:
  1) laravel-api/public/assets/img/iacademy-logo.png
  2) laravel-api/assets/img/iacademy-logo.png
- Result: not found ⇒ no logo.

Changes Implemented:
- Enhanced detectLogoPath() to:
  - Allow override via config('app.logo_path') or env APP_LOGO_PATH.
  - Include repo-root/assets/img/iacademy-logo.png as an additional candidate.
  - Use realpath() where possible, and file_exists() checks.
- File changed: laravel-api/app/Services/GradingSheetService.php

Checklist:
- [x] Analyze current PDF generation and path resolution.
- [x] Implement fallback search including mono-repo root and env/config override.
- [ ] Regenerate a grading sheet PDF and verify the logo appears top-left.
- [ ] If missing, confirm file existence at: c:/xampp8/htdocs/iacademy/cebu-iac-lms/assets/img/iacademy-logo.png
- [ ] If still missing, set an explicit override:
      - In .env: APP_LOGO_PATH="c:/xampp8/htdocs/iacademy/cebu-iac-lms/assets/img/iacademy-logo.png"
      - Or in config/app.php: 'logo_path' => env('APP_LOGO_PATH', null),
- [ ] Optionally, copy the logo to laravel-api/public/assets/img/iacademy-logo.png to keep public assets self-contained.
- [ ] Consider adding logging or debug output when detectLogoPath() fails (optional).
- [ ] Close task once logo verified in exported PDF.

Verification Steps:
1. Hit the Grading Sheet export endpoint (ReportsController::gradingSheetPdf) for a known student/term.
2. Open the resulting PDF and check the top-left for the logo.
3. If not visible, check laravel-api/storage/logs/laravel.log for any image exceptions from $pdf->Image().
4. If needed, set APP_LOGO_PATH as above and retry.

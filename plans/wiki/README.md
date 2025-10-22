# Internal Wiki — Staff User Guide

Audience: Internal staff only (Registrar, Admissions, Scholarship, Finance, Academics, Admin)

Purpose
- Provide version-controlled, role-gated documentation for using the system
- Live next to the codebase for accurate, reviewable updates
- Linked into the SPA as a Help/Docs section for convenient access

Structure
- Root: plans/wiki/
- Categories:
  - Registrar: plans/wiki/registrar/
  - Admissions: plans/wiki/admissions/
  - Scholarships: plans/wiki/scholarships/
  - Finance: plans/wiki/finance/
  - Academics: plans/wiki/academics/

Conventions
- Use clear headings, bullet lists, and short paragraphs
- Link to SPA routes using hash paths, e.g., #/registrar/enlistment
- Include screenshots under plans/wiki/images/... when available
- Keep docs scoped to internal behaviors and flows; exclude sensitive credentials

Index
- Registrar
  - Enlistment — plans/wiki/registrar/enlistment.md
  - Index — plans/wiki/registrar/index.md
- Admissions — plans/wiki/admissions/index.md
- Scholarships — plans/wiki/scholarships/index.md
- Finance — plans/wiki/finance/index.md
- Academics — plans/wiki/academics/index.md

How to add a new page
1) Create a new Markdown file under the appropriate category folder
2) Update the SPA DocsService categories config to add the page (label, key, path, roles)
3) Navigate to #/docs/{category}/{page} to verify render

Changelog
- v1: Initial setup with Registrar → Enlistment full guide and category stubs (Admissions, Scholarships, Finance, Academics)

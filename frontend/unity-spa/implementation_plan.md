# Implementation Plan

[Overview]
Adopt Montserrat as the default UI font and Gotham for headings/emphasis across the Unity SPA, loading both from local assets and applying global typography overrides.

This implementation introduces local @font-face declarations for the font families available under assets/fonts/Montserrat and assets/fonts/Gotham and wires them into the SPA via dedicated CSS files. We will ensure robust fallback stacks, correct weight/style mappings, and non-invasive overrides that do not break existing layouts. The change will be scoped to the SPA by linking new CSS from frontend/unity-spa/index.html and by providing targeted overrides for legacy selectors found in assets/css/site_global.css. The approach avoids changes to third-party libraries (Bootstrap, DataTables, etc.) and relies on cascade order and specificity to apply the desired typography.

[Types]  
No TypeScript or runtime type changes; introduce CSS design tokens for typography.

CSS custom properties (design tokens):
- In assets/css/typography.css
  - :root
    - --font-ui: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, 'Helvetica Neue', Helvetica, sans-serif
    - --font-heading: 'Gotham', 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, 'Helvetica Neue', Helvetica, sans-serif
    - --font-rounded: 'Gotham Rounded', 'Gotham', 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, 'Helvetica Neue', Helvetica, sans-serif

Font-family specifications (via @font-face in assets/css/fonts.css):
- Family: 'Montserrat'
  - 100 Thin (normal, italic)
  - 200 ExtraLight (normal, italic)
  - 300 Light (italic only present; normal will fallback to Regular’s metrics)
  - 400 Regular (normal, italic)
  - 500 Medium (normal, italic)
  - 600 SemiBold (normal, italic)
  - 700 Bold (normal, italic)
  - 900 Black (normal, italic)
- Family: 'Gotham'
  - 300 Light (normal, italic)
  - 400 Book (normal, italic)
  - 500 Medium (normal)
  - 700 Bold (normal, italic)
  - 900 Black (normal)
- Family: 'Gotham Rounded'
  - 400 Book (normal)
  - 700 Bold (normal)

[Files]
Add two new CSS files and link them into the SPA; non-destructive updates to one HTML file.

Detailed breakdown:
- New files to be created:
  - assets/css/fonts.css
    - Purpose: Local @font-face declarations for all Montserrat and Gotham (plus Gotham Rounded) weights available in assets/fonts/.
    - Notes: Use font-display: swap; map correct font-weight/font-style to filenames; WOFF format.
  - assets/css/typography.css
    - Purpose: Global typography layer for the SPA:
      - Establish CSS variables for font stacks
      - Set default UI font on body to Montserrat
      - Apply Gotham to headings (h1–h6) and strong title-like elements
      - Provide safe overrides for legacy classes (.nav, .h1, .h2, .p) defined in site_global.css
      - Do not modify third-party components
- Existing files to be modified:
  - frontend/unity-spa/index.html
    - Add two link tags to include ../../assets/css/fonts.css and ../../assets/css/typography.css (loaded after external CDNs and before SPA content usage)
- Files to be deleted or moved:
  - None
- Configuration file updates:
  - None (Tailwind is loaded via CDN; not reconfiguring Tailwind’s theme in this pass)

[Functions]
No JavaScript functions are created or modified; this is a CSS-only integration.

Detailed breakdown:
- New functions: None
- Modified functions: None
- Removed functions: None

[Classes]
No JavaScript or backend classes are affected.

Detailed breakdown:
- New classes: None
- Modified classes: None
- Removed classes: None

[Dependencies]
No new NPM/Composer packages; use local font files already present.

Details:
- Fonts are served from assets/fonts; formats are WOFF.
- font-display: swap to ensure good performance and prevent FOIT.
- No CDN fonts required; this reduces external dependency risk.

[Testing]
Manual and visual validation in key SPA routes and components.

Test file requirements: None. Validation strategies:
- Load frontend/unity-spa/index.html and verify:
  - body text renders in Montserrat (inspect computed font-family on body and a few paragraphs/spans).
  - h1–h6 render using Gotham with appropriate weights (verify computed styles and font-weight).
  - Legacy selectors (.nav, .h1, .h2, .p) are overridden without layout regressions.
  - Check RTL/edge cases (long titles, mixed weights).
- Browser matrix: Chrome (latest), Firefox (latest), Edge (latest).
- Performance: Verify no 404s on font assets, and ensure fonts.css is loaded before typography.css.
- Accessibility: Verify color/contrast unaffected; font-size inheritance intact.

[Implementation Order]
Introduce fonts, then apply and wire them with minimal risk.

1) Create assets/css/fonts.css with full @font-face declarations for Montserrat, Gotham, and Gotham Rounded using existing WOFF files.
2) Create assets/css/typography.css to define CSS variables and apply global typography (body default Montserrat; headings Gotham; overrides for .nav/.h1/.h2/.p).
3) Update frontend/unity-spa/index.html to link ../../assets/css/fonts.css and ../../assets/css/typography.css in the head (after external CSS but before content usage).
4) Smoke test locally: open SPA pages/routes (students, faculty, registrar, schedules) and visually verify typography.
5) Iterate on any missing weights/styles by mapping nearest-available weight or fallback to system sans-serif if a specific face is not present.

# Search Icon Visibility - Investigation and Fix Tracking

Context:
- Search icons not displaying on Curricula and Students pages.
- Templates use Font Awesome 5 classes (e.g., <i class="fas fa-search">).
- SPA head originally loaded FA from use.fontawesome.com only.

Root Causes Considered:
- CDN/SRI/crossorigin/network/CSP blocking FA CSS or webfonts.
- CSS override breaking `.fas` weight (FA5 solid icons require `font-weight: 900`).
- Color contrast/inheritance causing icon to appear invisible on neutral buttons.

Plan and Tasks:
1. Add fallback Font Awesome 5.15.4 (jsDelivr) after existing CDN and enforce weight. [DONE]
   - Added: <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
   - Added safety style: `.fas { font-weight: 900 !important; }`

2. Optional: Ensure explicit icon color on Curricula search button to improve contrast (e.g., `text-gray-600`). [PENDING]

3. Verify on pages:
   - Curricula: search button icon renders.
   - Students: header "Advanced Search" icon renders and other FA icons (spinner, edit, trash, ellipsis) render. [PENDING]

4. Optional: Vendor a local FA 5.15.4 copy (CSS + webfonts) to avoid external CDN dependency entirely. [PENDING]

Change Log:
- 2025-08-26: Implemented Task #1 in `frontend/unity-spa/index.html`.

# Global Term Selector Implementation Plan

## Overview
Implement a global term/semester selector that allows users to select an active term across the Unity SPA application. The selected term will persist in browser sessions and be accessible throughout the application.

## Information Gathered

### Current Implementation Analysis:
- **API Endpoint**: `/api/v1/generic/terms` exists in `GenericApiController.php`
- **Data Source**: `tb_mas_sy` table via `SchoolYear` model
- **Current Usage**: Terms are loaded individually in `StudentsController` and `ViewerController`
- **Data Structure**: Terms include `intID`, `enumSem`, `strYearStart`, `strYearEnd`, `term_label`, `label`
- **Frontend**: AngularJS 1.x application with modular structure
- **Storage**: `StorageService` exists for localStorage operations

### Current Term Data Format:
```json
{
  "intID": 123,
  "enumSem": "1st",
  "strYearStart": "2024",
  "strYearEnd": "2025", 
  "term_label": "Semester",
  "term_student_type": null,
  "label": "1st Sem 2024-2025"
}
```

## Plan

### Backend Changes (Laravel API)

#### 1. Add Active Term Endpoint
- **File**: `laravel-api/app/Http/Controllers/Api/V1/GenericApiController.php`
- **New Methods**:
  - `getActiveTerm()` - Returns the current active term
  - Logic: Get most recent term by `strYearStart` DESC, `enumSem` ASC

#### 2. Update API Routes
- **File**: `laravel-api/routes/api.php`
- **New Route**: `GET /api/v1/generic/active-term`

### Frontend Changes (Unity SPA)

#### 3. Create Global Term Service
- **File**: `frontend/unity-spa/core/term.service.js`
- **Responsibilities**:
  - Load available terms from API
  - Get/set selected term with localStorage persistence
  - Broadcast term changes to other components
  - Get active term from API as default

#### 4. Create Term Selector Component
- **Files**: 
  - `frontend/unity-spa/shared/components/term-selector/term-selector.directive.js`
  - `frontend/unity-spa/shared/components/term-selector/term-selector.controller.js`
  - `frontend/unity-spa/shared/components/term-selector/term-selector.html`
- **Features**:
  - Dropdown selector for available terms
  - Display current selected term
  - Compact sidebar-friendly design

#### 5. Create Sidebar Component
- **Files**:
  - `frontend/unity-spa/shared/components/sidebar/sidebar.directive.js`
  - `frontend/unity-spa/shared/components/sidebar/sidebar.controller.js`
  - `frontend/unity-spa/shared/components/sidebar/sidebar.html`
- **Features**:
  - Contains term selector
  - Collapsible/expandable
  - Positioned on left side of main content

#### 6. Update Main Layout
- **File**: `frontend/unity-spa/index.html`
- **Changes**:
  - Add sidebar component
  - Adjust main content area for sidebar space
  - Add CSS for sidebar layout

#### 7. Update Existing Controllers
- **Files**:
  - `frontend/unity-spa/features/students/students.controller.js`
  - `frontend/unity-spa/features/students/viewer.controller.js`
  - `frontend/unity-spa/features/dashboard/dashboard.controller.js`
- **Changes**:
  - Remove individual term loading logic
  - Use global TermService for selected term
  - Listen for term change events

#### 8. Update Templates
- **Files**:
  - `frontend/unity-spa/features/students/students.html`
  - `frontend/unity-spa/features/dashboard/dashboard.html`
- **Changes**:
  - Remove local term dropdowns
  - Use global selected term from service

### Dependent Files to be Modified:
1. **Backend**: `GenericApiController.php`, `routes/api.php`
2. **Frontend Core**: New `term.service.js`
3. **Frontend Components**: New sidebar and term-selector components
4. **Frontend Layout**: `index.html` for sidebar integration
5. **Frontend Controllers**: Students, viewer, dashboard controllers
6. **Frontend Templates**: Students and dashboard templates

### Implementation Steps:
1. **Backend API Enhancement** - Add active term endpoint
2. **Core Service Creation** - Create TermService for global state management
3. **Component Development** - Build term selector and sidebar components
4. **Layout Integration** - Add sidebar to main layout
5. **Controller Updates** - Refactor existing controllers to use global service
6. **Template Updates** - Remove local term selectors, use global state
7. **Testing & Validation** - Ensure term selection works across all components

### Technical Specifications:
- **Persistence**: Browser localStorage via existing StorageService
- **Default Selection**: Most recent term from API
- **State Management**: AngularJS service with $rootScope broadcasting
- **UI Framework**: Existing Tailwind CSS classes
- **API Integration**: Existing $http service patterns

### Follow-up Steps:
- Test term selection persistence across browser sessions
- Verify term changes update all dependent components
- Test API endpoint for active term retrieval
- Validate responsive design for sidebar on different screen sizes
- Add error handling for term loading failures

## Success Criteria:
1. ✅ Global term selector appears in sidebar on all pages
2. ✅ Selected term persists across browser sessions
3. ✅ Term changes automatically update all components using terms
4. ✅ API provides current active term as default selection
5. ✅ Existing term-dependent functionality continues to work
6. ✅ Responsive design maintains usability on all screen sizes

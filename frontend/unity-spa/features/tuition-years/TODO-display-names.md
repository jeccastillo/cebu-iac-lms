# Tuition Years Edit: Show Names Instead of IDs

Scope:
- Replace raw IDs with human-readable names for Tracks, Programs, and Subjects on the Tuition Years edit page.

Plan and Status:

1) Controller enhancements (frontend/unity-spa/features/tuition-years/tuition-years.controller.js)
   - Add lookup maps: _shsProgramMap, _collegeProgramMap, _subjectMap. [DONE]
   - Add helpers: lookupShsProgram(id), lookupCollegeProgram(id), lookupSubject(id). [DONE]
   - Build maps after option lists load using buildMap(). [DONE]
   - Ensure buildMap accepts both numeric and string keys to avoid type mismatch. [DONE]

2) Template updates (frontend/unity-spa/features/tuition-years/edit.html)
   - Change table headers:
     - Track ID -> Track [DONE]
     - Program ID -> Program [DONE]
     - Subject ID -> Subject [DONE]
   - Replace bindings:
     - SHS Tracks: {{ vm.lookupShsProgram(t.track_id) }} [DONE]
     - College Programs: {{ vm.lookupCollegeProgram(p.track_id) }} [DONE]
     - SHS Electives: {{ vm.lookupSubject(e.subject_id) }} [DONE]
   - Remove a duplicated opening wrapper div detected during diff. [DONE]

Validation checklist:
- Existing rows render names after options load. [DONE]
- Adding new rows via dropdowns remains unchanged. [DONE]
- No console errors; helpers exist on vm. [DONE]

Files touched:
- frontend/unity-spa/features/tuition-years/tuition-years.controller.js
- frontend/unity-spa/features/tuition-years/edit.html

# Implementation Plan

[Overview]
The goal is to modify the Students list page to enable dynamic server-side searching for student records based on user input in the column filters. This will replace the current client-side filtering mechanism, allowing for more efficient and accurate searches across potentially large datasets.

This implementation is necessary to improve performance and user experience, ensuring that users can quickly find students without loading all records into the client.

[Types]  
No changes to the type system are required. The existing data structures will remain intact.

[Files]
The following files will be modified:
- **frontend/unity-spa/features/students/students.controller.js**
  - Update the `onColumnFilterChange` method to trigger a server-side search instead of filtering loaded rows.
  - Modify the `buildParams` method to include new parameters for the server-side search.
  
- **frontend/unity-spa/features/students/students.html**
  - Update the input fields for column filters to call the new search method on change.
  - Remove any client-side filtering logic that relies on `vm.filteredRows()`.

- **laravel-api/app/Http/Controllers/Api/V1/StudentController.php**
  - Modify the `index` method to accept new query parameters for server-side filtering based on the column inputs.

[Functions]
- **New functions:**
  - None.

- **Modified functions:**
  - **students.controller.js**
    - `onColumnFilterChange()`: Update to call the server-side search.
    - `buildParams()`: Add parameters for each column filter.
  
  - **StudentController.php**
    - `index(Request $request)`: Modify to handle new query parameters for filtering.

- **Removed functions:**
  - None.

[Classes]
No new classes will be created. Existing classes will be modified as needed.

[Dependencies]
No new dependencies will be introduced. Existing services and libraries will be utilized.

[Testing]
- **Manual Testing:**
  - Verify that typing in any column filter triggers a server-side search.
  - Ensure that the results update correctly based on the filters applied.
  - Test pagination to confirm that it works seamlessly with the new search functionality.

- **Automated Testing:**
  - Update or create unit tests for the `index` method in `StudentController` to ensure it handles the new query parameters correctly.
  - Test the `StudentsService` to confirm that it correctly calls the API with the new parameters.

[Implementation Order]
1. Update `students.controller.js` to modify the `onColumnFilterChange` and `buildParams` methods.
2. Update `students.html` to wire the input fields to the new search method.
3. Modify `StudentController.php` to handle the new query parameters in the `index` method.
4. Perform manual testing to ensure the new functionality works as expected.
5. Update or create automated tests to cover the new functionality.

task_progress Items:
- [ ] Step 1: Update students.controller.js to modify the onColumnFilterChange and buildParams methods.
- [ ] Step 2: Update students.html to wire the input fields to the new search method.
- [ ] Step 3: Modify StudentController.php to handle the new query parameters in the index method.
- [ ] Step 4: Perform manual testing to ensure the new functionality works as expected.
- [ ] Step 5: Update or create automated tests to cover the new functionality.

# TODO — Classlist CRUD (tb_mas_classlist) with System Logs and Dissolve Guard

Scope:
- Add CRUD endpoints for tb_mas_classlist using App\Models\Classlist
- Always blank: strClassName, year, strSection, sub_section on create/update
- Record system logs for create/update/dissolve
- Legacy delete behavior: dissolve (set isDissolved=1) when no connected tb_mas_classlist_student rows; otherwise 422
- Role-protect write operations: registrar,admin
- Critical-path testing only (per user)

Checklist:
- [ ] 1. Create request: app/Http/Requests/Api/V1/ClasslistStoreRequest.php
- [ ] 2. Create request: app/Http/Requests/Api/V1/ClasslistUpdateRequest.php
- [ ] 3. Implement controller: app/Http/Controllers/Api/V1/ClasslistController.php
      - [ ] index(): list with filters, exclude dissolved by default
      - [ ] show(): fetch by intID
      - [ ] store(): create with restricted fields blanked, intFinalized default 0, log create
      - [ ] update(): restricted fields blanked, log update
      - [ ] destroy(): dissolve (isDissolved=1) with guard; log update (dissolve)
- [ ] 4. Wire endpoints in routes/api.php (prefix /api/v1):
      - [ ] GET  /classlists
      - [ ] GET  /classlists/{id}
      - [ ] POST /classlists (role: registrar,admin)
      - [ ] PUT  /classlists/{id} (role: registrar,admin)
      - [ ] DELETE /classlists/{id} (role: registrar,admin) → dissolve
- [ ] 5. Critical-path tests (manual or curl):
      - [ ] POST create → 201, restricted fields "", intFinalized default 0, system log create
      - [ ] PUT update (attempt to set restricted fields) → remain "", system log update
      - [ ] GET list + filters, GET show (404 for unknown)
      - [ ] DELETE dissolve guard:
            - [ ] with students -> 422, no dissolve
            - [ ] without students -> 200, isDissolved=1, system log update
      - [ ] Role middleware blocks unauthorized writes
- [ ] 6. Update this TODO as steps are completed

Notes:
- Use App\Services\SystemLogService::log(action, 'Classlist', id, old, new, request)
- Maintain compatibility with existing ClasslistService which filters isDissolved=0

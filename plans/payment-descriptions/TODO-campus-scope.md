# Payment Descriptions: Campus Scope Implementation

Task: Add campus_id field to payment_descriptions. On create, resolve and save the globally selected campus_id. On retrieval, default-filter by the globally selected campus_id.

Approved Plan Steps:
1. Migration
   - Add nullable, indexed campus_id column to payment_descriptions with optional FK to tb_mas_campuses(id).
2. Model
   - Add campus_id to $fillable and cast as integer.
3. Resource
   - Expose campus_id in PaymentDescriptionResource.
4. Requests
   - Store/Update: accept campus_id as sometimes|nullable|integer.
5. Controller
   - index(): If campus_id not provided in query, resolve campus_id (input -> header X-Campus-ID -> cashier via X-Faculty-ID) and filter by it.
   - store(): If campus_id not provided in body, resolve with same precedence and set before create().
   - update(): Allow campus_id to be updated when provided (no defaulting required).
6. Migrate & Test
   - Run php artisan migrate in laravel-api directory.
   - Test create & list behavior with/without X-Campus-ID header and explicit campus_id.

Progress:
- [x] Migration: 2025_08_31_021000_add_campus_id_to_payment_descriptions.php
- [ ] Model: add campus_id fillable/cast
- [ ] Resource: include campus_id
- [ ] Requests: add campus_id validation rules
- [ ] Controller: implement campus resolution/filtering in index() and store()
- [ ] Run migrations and smoke test endpoints

Resolution Precedence (reference):
1) request input campus_id
2) header X-Campus-ID
3) acting cashier campus via X-Faculty-ID header

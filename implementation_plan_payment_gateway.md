# Implementation Plan

[Overview]
Build a new Laravel-first payment gateway flow that recreates and modernizes PaymentGatewayController.php using existing Eloquent models, integrates Paynamics (onlinebanktransfer, wallet, nonbank_otc), BDO/CyberSource (bdo_pay), and MaxxPayment installments (maxx_payment), excludes Maya, exposes API endpoints with webhooks/callbacks modeled after PaynamicsWebhookController.php, seeds payment_modes (excluding Maya), and adds an AngularJS frontend checkout UI with success/failure/cancel pages.

This plan migrates legacy CI-style logic in PaymentGatewayController.php to Laravel under app/Http/Controllers/Api/V1, ensuring it works with the available models in laravel-api/app/Models. It centralizes environment/config (merchant IDs, keys, endpoints) to config/payments.php with .env keys, adds robust validation and signature computations for BDO/CyberSource, composes Paynamics payloads for onlinebanktransfer/wallet/nonbank_otc (including OTC instructions), and integrates MaxxPayment redirect link generation. The API returns either a link or a set of post fields needed by the frontend to redirect/auto-submit to the provider, and webhooks update PaymentDetail record status and trigger email notifications mirroring the legacy webhook behavior. The AngularJS frontend (unity-spa) adds routes for checkout and result pages using $routeProvider and a payments.service.js that talks to the Laravel API.

[Types]  
The solution introduces strict request/response contracts for checkout and webhooks.

Backend DTO-like array shapes (request validation rules to enforce types):
- CheckoutRequest (POST /api/v1/payments/checkout)
  - student_information_id: int|required|exists:admission_student_informations,id (or appropriate PK)
  - student_number: string|nullable
  - first_name: string|required
  - middle_name: string|nullable
  - last_name: string|required
  - email: email|required
  - contact_number: string|required
  - description: string|required
  - remarks: string|nullable
  - mode_of_payment_id: int|required|exists:payment_modes,id
  - total_price_without_charge: numeric|required|min:0
  - total_price_with_charge: numeric|required|min:0
  - charge: numeric|required|min:0
  - mailing_fee: numeric|nullable|min:0
  - order_items: array|required|min:1
    - order_items[].id: int|required
    - order_items[].title: string|required
    - order_items[].qty: int|required|min:1
    - order_items[].price_default: numeric|required|min:0
    - order_items[].term: string|nullable
    - order_items[].academic_year: string|nullable
  - bill_to_forename: string|required_if:pmethod,bdo_pay
  - bill_to_surname: string|required_if:pmethod,bdo_pay
  - bill_to_email: email|required_if:pmethod,bdo_pay
  - dob: date|nullable

- CheckoutResponse (JSON)
  - success: boolean
  - gateway: enum['paynamics','bdo_pay','maxx_payment']
  - request_id: string
  - payment_link: string|null (for redirect-style gateways: paynamics direct link, paynamics OTC pay_reference when applicable, or maxx link)
  - post_data: object|null (for BDO/CyberSource signed form fields)
  - notification_url: string|null
  - response_url: string|null
  - cancel_url: string|null
  - message: string|null
  - data: object|null (raw gateway response for logs/debug)

- WebhookRequest shapes (provider-specific)
  - Paynamics:
    - request_id: string|required
    - response_message: string|required
    - response_advise: string|nullable
    - date_paid: string|nullable
    - additional provider fields as-is
  - BDO/CyberSource:
    - req_reference_number: string|required
    - decision: enum['ACCEPT','DECLINE',...]|required
    - message: string|nullable
    - date_paid: string|nullable
  - MaxxPayment:
    - sc_values: string JSON with keys SC_REF (request id) and SC_STATUS ['approved','declined','declined2','down']

Frontend Type Contracts:
- AngularJS PaymentsService:
  - getPaymentModes(): Promise<PaymentMode[]>
  - checkout(payload: CheckoutRequest): Promise<CheckoutResponse>

[Files]
Introduce new Laravel controllers and config, seeders for payment_modes, and AngularJS feature modules and templates without overwriting existing general plan files.

Detailed breakdown:
- New files (backend)
  - laravel-api/app/Http/Controllers/Api/V1/PaymentGatewayController.php
    - New, Laravel-native controller that recreates legacy PaymentGatewayController@pay with clean methods per pmethod.
  - laravel-api/app/Http/Controllers/Api/V1/PaymentsWebhookController.php
    - New, consolidates webhook endpoints for Paynamics, BDO/CyberSource, MaxxPayment, modeled after the provided PaynamicsWebhookController.php.
  - laravel-api/app/Http/Requests/Api/V1/Payments/CheckoutRequest.php
    - New FormRequest with validation rules described above.
  - laravel-api/config/payments.php
    - New config file centralizing merchant IDs, keys, endpoints for local/staging/prod with env fallbacks.
  - laravel-api/database/seeders/PaymentModeSeeder.php
    - New seeder to insert modes for: paynamics onlinebanktransfer, paynamics wallet, paynamics nonbank_otc, bdo_pay, maxx_payment (no Maya).
- Existing files to be modified (backend)
  - laravel-api/routes/api.php
    - Add routes:
      - POST /api/v1/payments/checkout -> PaymentGatewayController@checkout
      - POST /api/v1/payments/cancel -> PaymentGatewayController@cancel
      - POST /api/v1/payments/webhook/paynamics -> PaymentsWebhookController@paynamics
      - POST /api/v1/payments/webhook/bdo -> PaymentsWebhookController@bdo
      - POST /api/v1/payments/webhook/maxx -> PaymentsWebhookController@maxx
  - laravel-api/database/seeders/DatabaseSeeder.php
    - Call PaymentModeSeeder.
- New files (frontend)
  - frontend/unity-spa/features/payments/payments.service.js
    - Calls Laravel API for /payments/checkout and reads /payment-modes.
  - frontend/unity-spa/features/payments/checkout.controller.js
  - frontend/unity-spa/features/payments/checkout.html
  - frontend/unity-spa/features/payments/result.controller.js
  - frontend/unity-spa/features/payments/success.html
  - frontend/unity-spa/features/payments/failure.html
  - frontend/unity-spa/features/payments/cancel.html
  - Optionally: frontend/unity-spa/features/payments/bdo-autoform.directive.js (helper to auto-submit the BDO/CyberSource form).
- Existing files to be modified (frontend)
  - frontend/unity-spa/core/routes.js
    - Add routes:
      - /payments/checkout
      - /payments/success
      - /payments/failure
      - /payments/cancel
- Files to be deleted or moved
  - None in this change. The legacy PaymentGatewayController.php and PaynamicsWebhookController.php at repository root are references only.
- Configuration file updates
  - Add env keys in laravel-api/.env.example and/or document for .env:
    - PAYNAMICS_MERCHANT_ID
    - PAYNAMICS_MKEY
    - PAYNAMICS_USERNAME
    - PAYNAMICS_PASSWORD
    - PAYNAMICS_URL_PROD / PAYNAMICS_URL_STAGING
    - BDO_ACCESS_KEY
    - BDO_PROFILE_ID
    - BDO_SECRET_KEY
    - BDO_URL (secureacceptance URL)
    - MAXX_URL_PROD / MAXX_URL_STAGING
    - MAXX_MC_CODE (SC_MC)
    - MAXX_OPTIONS_JSON

[Functions]
Add gateway-specific builders, signature utilities, and webhook handlers; modify routes to wire them.

Detailed breakdown:
- New functions
  - PaymentGatewayController@checkout(Request $request): JsonResponse
    - Validates via CheckoutRequest.
    - Loads PaymentMode and AdmissionStudentInformation.
    - Recomputes/validates charge logic (percentage vs fixed, min charge floor for certain modes if required).
    - Persists PaymentDetail and PaymentItems-like rows (if PaymentItem model exists; otherwise track in PaymentDetail JSON metadata).
    - Branch by pmethod:
      - pmethod in ['onlinebanktransfer','wallet','nonbank_otc'] -> build Paynamics payload (transaction, customer_info, order_details), sign rawTrx + customer signature, POST to configured Paynamics URL with HTTP Basic auth, handle JSON response, persist response fields. Return link or OTC reference to client.
      - pmethod == 'bdo_pay' -> build CyberSource payload with signed_field_names per legacy, compute signature HMAC-SHA256 with BDO_SECRET_KEY, return post_data to client for auto-submit to BDO URL.
      - pmethod == 'maxx_payment' -> construct URL with SC parameters (SC_MC, SC_AMOUNT, SC_REF, SC_OPTIONS, SC_SUCCESSURL/FAILURL/CANCELURL), POST as legacy required, parse response to obtain link, return payment_link.
  - PaymentGatewayController@cancel(Request $request): JsonResponse
    - Parity with legacy cancelTransaction: calls Paynamics cancel API request (org_request_id etc.), updates PaymentDetail status, returns result.
  - PaymentsWebhookController@paynamics(Request $request)
    - Reference provided PaynamicsWebhookController::webhook. Update PaymentDetail status: Paid / expired / other, dates, remarks = 'Paynamics', send emails via PaymentDetail::sendEmailAfterPayment or sendEmailExpired, optionally update student info status logic (Reserved / Waiting For Interview) if PaymentDetail->studentInfo is mapped. Return 200 JSON.
  - PaymentsWebhookController@bdo(Request $request)
    - Reference PaynamicsWebhookController::webhook_bdo. Check decision 'ACCEPT' -> mark Paid and send emails; 'DECLINE' -> mark Declined; else reflect decision. Remarks 'BDO Pay'. Redirect to frontend #/payments/success|failure depending on outcome.
  - PaymentsWebhookController@maxx(Request $request)
    - Reference PaynamicsWebhookController::webhook_maxxpayment. Parse sc_values JSON. Handle SC_STATUS approved/declined/declined2/down and redirect accordingly. Remarks 'BDO installment'.
  - Helpers within PaymentGatewayController (private):
    - buildPaynamicsOrderLines(array $order_items, float $subtotal, float $mailingFee, string $pmethod, string $pchannel): array
    - signPaynamicsTransaction(string $merchantid, array $trx, string $mkey): string
    - signCustomer(string $fname, string $lname, string $mname, string $email, string $phone, string $mobile, ?string $dob, string $mkey): string
    - httpJson(string $url, array $payload, ?string $username = null, ?string $password = null): array
    - buildBdoFields(array $context): array (sets signed_field_names and computes signature)
    - buildMaxxUrl(array $context): string
- Modified functions
  - routes/api.php: register new routes with middleware as appropriate (public endpoints for checkout and webhooks).
- Removed functions
  - None.

[Classes]
Add two new controllers and a request class; models remain unchanged and are reused.

Detailed breakdown:
- New classes
  - App\Http\Controllers\Api\V1\PaymentGatewayController
    - Methods: checkout, cancel, and private helper methods described above.
  - App\Http\Controllers\Api\V1\PaymentsWebhookController
    - Methods: paynamics, bdo, maxx.
  - App\Http\Requests\Api\V1\Payments\CheckoutRequest
    - Rules: as specified under Types.
- Modified classes
  - None of the existing Models need changes for MVP. If PaymentItem or PaymentOrderItem are not present in laravel-api, persist just PaymentDetail plus flatten items into a JSON column on PaymentDetail or skip child records (aligned to current PaymentDetail stub). This plan assumes keeping child rows optional.
- Removed classes
  - None.

[Dependencies]
No new Composer packages are strictly required; use native PHP curl and Laravel HTTP client. Optional enhancements listed below but not mandatory for MVP.

Details:
- PHP extensions: curl enabled.
- Optional:
  - Use Laravel HTTP client (Illuminate\Support\Facades\Http) instead of curl for readability.
  - Consider installing cybersource sdk if needed in the future; not required now because Secure Acceptance uses signed form POST.

[Testing]
Testing will be focused on API contract and webhook side-effects.

- Backend
  - Feature tests:
    - tests/Feature/Payments/CheckoutTest.php
      - Validates request, creates PaymentDetail, branches by pmethod (mock HTTP to Paynamics/Maxx).
    - tests/Feature/Payments/WebhookPaynamicsTest.php
      - Simulate Paynamics webhook success/expired; assert PaymentDetail status and emails queued (use Mail::fake()).
    - tests/Feature/Payments/WebhookBdoTest.php
      - Simulate decision ACCEPT/DECLINE; assert status, redirection URL computed.
    - tests/Feature/Payments/WebhookMaxxTest.php
      - Simulate approved/declined/down; assert status and redirect.
- Frontend
  - Manual verification:
    - New routes render, payment modes load via /api/v1/payment-modes, checkout posts payload, BDO flow auto-submits to provider URL, success/failure/cancel pages display appropriate messages.
  - Lint existing AngularJS files, ensure routes.js updated correctly.

[Implementation Order]
Implement backend controllers and config first, then seeders, then frontend integration, then tests.

1) Backend config
   - Add config/payments.php with environment-driven settings and sensible defaults for local/staging/prod.

2) Controllers and Request
   - Create CheckoutRequest.
   - Implement PaymentGatewayController with methods and helpers.
   - Implement PaymentsWebhookController for paynamics, bdo, maxx.

3) Routes
   - Update laravel-api/routes/api.php to add the new endpoints.

4) Seeders
   - Create PaymentModeSeeder and wire in DatabaseSeeder. Seed entries (examples — adjust charges/images as needed):
     - Paynamics Online Bank Transfer
       - name: 'Paynamics Online Bank Transfer', pmethod: 'onlinebanktransfer', pchannel: 'ubp_online', type: 'percentage', charge: 0, is_nonbank: false, is_active: true
     - Paynamics Wallet
       - name: 'Paynamics Wallet', pmethod: 'wallet', pchannel: 'gcash', type: 'percentage', charge: 0, is_nonbank: false, is_active: true
     - Paynamics OTC
       - name: 'Paynamics OTC (Non-bank)', pmethod: 'nonbank_otc', pchannel: '711_ph', type: 'fixed', charge: 0, is_nonbank: true, is_active: true
     - BDO/CyberSource
       - name: 'BDO Pay (Credit/Debit)', pmethod: 'bdo_pay', pchannel: 'bdo_cybersource', type: 'fixed', charge: 0, is_nonbank: false, is_active: true
     - MaxxPayment Installments
       - name: 'BDO Installment (MaxxPayment)', pmethod: 'maxx_payment', pchannel: 'maxx', type: 'fixed', charge: 0, is_nonbank: false, is_active: true
     - Explicitly exclude any mode with pmethod 'maya_pay'.

5) Frontend AngularJS
   - Add payments.service.js with methods getPaymentModes and checkout.
   - Add checkout.controller.js and checkout.html displaying:
     - mode selection (filter out maya_pay)
     - items summary, computed fees/charges
     - on submit: call API and branch:
       - bdo_pay: receive post_data -> render a hidden form with action BDO_URL; auto-submit via directive or controller.
       - paynamics onlinebanktransfer/wallet: redirect to payment_link (payment_action_info).
       - paynamics nonbank_otc: show pay_reference and email notice; still show a "Go to instructions" link if applicable.
       - maxx_payment: redirect to payment_link.
   - Add result.controller.js with success/failure/cancel pages.
   - Update core/routes.js to register:
     - /payments/checkout -> payments/checkout.html
     - /payments/success -> payments/success.html
     - /payments/failure -> payments/failure.html
     - /payments/cancel -> payments/cancel.html

6) Webhook Redirect Targets
   - In webhook handlers, after status update, redirect to frontend routes:
     - success -> https://<host>/#/payments/success
     - failure/declined -> https://<host>/#/payments/failure
     - cancel/expired/down -> https://<host>/#/payments/cancel
   - These replace legacy redirects to sms-makati/cebu domains.

7) Documentation and ENV
   - Update README or a docs/payments.md with env variable descriptions.
   - Add example .env keys.

8) Tests
   - Add Laravel feature tests with Mail::fake and Http::fake for gateway calls.

9) Smoke Verification
   - Seed payment modes.
   - Load /payments/checkout UI; test each mode through to webhook callback using manual/local stubs.

task_progress Items:
- [ ] Step 1: Add config/payments.php and document required .env variables
- [ ] Step 2: Create CheckoutRequest with validation rules
- [ ] Step 3: Implement PaymentGatewayController@checkout and helpers (Paynamics/BDO/Maxx)
- [ ] Step 4: Implement PaymentGatewayController@cancel (Paynamics cancel)
- [ ] Step 5: Implement PaymentsWebhookController for paynamics, bdo, maxx with status updates and redirects
- [ ] Step 6: Update laravel-api/routes/api.php with new endpoints
- [ ] Step 7: Create PaymentModeSeeder and wire to DatabaseSeeder
- [ ] Step 8: Build AngularJS payments.service.js and checkout UI (controller + template)
- [ ] Step 9: Add result pages (success/failure/cancel) and route entries
- [ ] Step 10: Write Laravel feature tests for checkout and webhooks
- [ ] Step 11: Run seeder and perform end-to-end smoke tests locally

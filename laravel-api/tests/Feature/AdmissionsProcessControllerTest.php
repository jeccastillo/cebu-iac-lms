<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\UploadedFile;

use App\Http\Controllers\Api\V1\AdmissionProcessController;

use App\Models\Admissions\AdmissionStudentInformation;
use App\Models\Admissions\AdmissionStudentType;
use App\Models\Admissions\AdmissionDesiredProgram;
use App\Models\Admissions\AdmissionUploadType;
use App\Models\Admissions\AdmissionFile;
use App\Models\Admissions\StudentInformationRequirement;
use App\Models\Admissions\AcceptanceLetterAttachment;

use App\Mail\Admissions\SubmitInformationMail;
use App\Mail\Admissions\SubmitRequirementsMail;
use App\Mail\Admissions\SendAcceptanceLetterMail;

class AdmissionsProcessControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register temporary test-only routes mapping to controller actions
        Route::middleware('api')->prefix('api/v1/test/admissions')->group(function () {
            Route::get('/student-types', [AdmissionProcessController::class, 'getStudentTypes']);
            Route::get('/desired-programs', [AdmissionProcessController::class, 'getDesiredPrograms']);

            Route::post('/store-information', [AdmissionProcessController::class, 'storeInformation']);
            Route::get('/information/{slug}', [AdmissionProcessController::class, 'viewInformation']);
            Route::get('/information-admissions/{slug}', [AdmissionProcessController::class, 'viewInformationForAdmission']);

            Route::post('/upload-requirements', [AdmissionProcessController::class, 'uploadRequirements']);
            Route::delete('/file/{id}', [AdmissionProcessController::class, 'deleteFile']);
            Route::post('/save-requirements', [AdmissionProcessController::class, 'saveRequirements']);

            Route::patch('/information/{id}', [AdmissionProcessController::class, 'updateInformation']);
            Route::patch('/information/{id}/status', [AdmissionProcessController::class, 'updateInformationStatus']);
            Route::patch('/information/{id}/remarks', [AdmissionProcessController::class, 'updateInformationRemarks']);

            Route::post('/information/{id}/attachments', [AdmissionProcessController::class, 'uploadAttachments']);
            Route::delete('/attachment/{id}', [AdmissionProcessController::class, 'deleteAttachment']);
            Route::post('/information/{id}/send-acceptance', [AdmissionProcessController::class, 'sendAcceptanceMail']);
        });

        // Sanity check necessary tables exists after migrations
        $requiredTables = [
            'admission_student_types',
            'admission_desired_programs',
            'admission_upload_types',
            'admission_student_upload_types',
            'admission_student_informations',
            'admission_student_applying_and_programs',
            'admission_files',
            'student_information_requirements',
            'acceptance_letter_attachments',
        ];

        foreach ($requiredTables as $t) {
            $this->assertTrue(Schema::hasTable($t), "Missing expected table: {$t}. Run migrations?");
        }
    }

    protected function seedBasics(): array
    {
        $type = new AdmissionStudentType();
        $type->title = 'UG - Freshman';
        $type->type = 'ug_freshman';
        $type->save();

        $program = new AdmissionDesiredProgram();
        $program->title = 'BS Computer Science';
        $program->type = 'undergraduate';
        $program->save();

        // Create common upload types (keys used in resources)
        $keys = ['valid_id', 'psa', 'tor', 'passport', 'payment', 'reservation_fee', 'report_card', 'good_moral_certificate', 'waiver', 'initial_fee'];
        $uploadTypes = [];
        $order = 1;
        foreach ($keys as $k) {
            $ut = new AdmissionUploadType();
            $ut->key = $k;
            $ut->label = strtoupper(str_replace('_', ' ', $k));
            $ut->order = $order++;
            $ut->save();
            $uploadTypes[] = $ut;
        }

        // Attach a subset to student type via pivot
        foreach (array_slice($uploadTypes, 0, 5) as $ut) {
            \DB::table('admission_student_upload_types')->insert([
                'admission_student_type_id' => $type->id,
                'admission_upload_type_id' => $ut->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return compact('type', 'program', 'uploadTypes');
    }

    public function test_get_student_types_returns_list(): void
    {
        $this->seedBasics();

        // Add another type
        $type2 = new AdmissionStudentType();
        $type2->title = 'UG - Transferee';
        $type2->type = 'ug_transferee';
        $type2->save();

        $res = $this->getJson('/api/v1/test/admissions/student-types')
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('data', $res);
        $this->assertGreaterThanOrEqual(2, count($res['data']));
        $this->assertArrayHasKey('id', $res['data'][0]);
        $this->assertArrayHasKey('title', $res['data'][0]);
        $this->assertArrayHasKey('type', $res['data'][0]);
    }

    public function test_get_desired_programs_excludes_others(): void
    {
        $this->seedBasics();

        $p2 = new AdmissionDesiredProgram();
        $p2->title = 'Some Other Program';
        $p2->type = 'others';
        $p2->save();

        $res = $this->getJson('/api/v1/test/admissions/desired-programs')
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('data', $res);
        $titles = array_map(fn ($r) => $r['title'], $res['data']);
        $this->assertContains('BS Computer Science', $titles);
        $this->assertNotContains('Some Other Program', $titles);
    }

    public function test_store_information_validates_and_creates_record_and_sends_mail(): void
    {
        Mail::fake();
        $seed = $this->seedBasics();
        $type = $seed['type'];
        $program = $seed['program'];

        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'email' => 'john.doe@example.com',
            'email_confirmation' => 'john.doe@example.com',
            'school' => 'Test High',
            'mobile_number' => '09123456789',
            'tel_number' => '1234567',
            'type_id' => $type->id,
            'program_id' => $program->id,
        ];

        $res = $this->post('/api/v1/test/admissions/store-information', $payload)
            ->assertOk()
            ->json();

        $this->assertTrue($res['success'] ?? false, 'Expected success true');
        $this->assertDatabaseHas('admission_student_informations', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Mail::assertSent(SubmitInformationMail::class, 1);
    }

    public function test_store_information_validation_failure(): void
    {
        $this->seedBasics();
        $payload = [
            'first_name' => '',
            'last_name' => '',
            // missing email fields
        ];

        $res = $this->post('/api/v1/test/admissions/store-information', $payload)
            ->assertOk()
            ->json();

        $this->assertFalse($res['success'] ?? true, 'Expected validation to fail');
        $this->assertArrayHasKey('response', $res);
    }

    public function test_view_information_by_slug_returns_resource(): void
    {
        $seed = $this->seedBasics();
        $info = new AdmissionStudentInformation();
        $info->first_name = 'Alice';
        $info->last_name = 'Smith';
        $info->email = 'alice@example.com';
        $info->type_id = $seed['type']->id;
        $info->program_id = $seed['program']->id;
        $info->slug = \Str::uuid();
        $info->save();

        $res = $this->getJson('/api/v1/test/admissions/information/' . $info->slug)
            ->assertOk()
            ->json();

        $this->assertTrue($res['success'] ?? false);
        $this->assertEquals('Alice', $res['data']['first_name']);
        $this->assertEquals('Smith', $res['data']['last_name']);
    }

    public function test_upload_requirements_stores_file_and_returns_resource(): void
    {
        // Controller uses default disk with "public/..." subpath; default is local
        Storage::fake('local');

        $file = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $res = $this->post('/api/v1/test/admissions/upload-requirements', [
            'file' => $file,
        ])->assertOk()->json();

        $this->assertTrue($res['success'] ?? false);
        $this->assertArrayHasKey('data', $res);
        $this->assertArrayHasKey('id', $res['data']);
        $this->assertEquals('pdf', $res['data']['filetype']);
        $this->assertDatabaseCount('admission_files', 1);
    }

    public function test_delete_file_removes_file_and_db_and_requirement(): void
    {
        Storage::fake('local');

        // Create a student info and upload type
        $seed = $this->seedBasics();
        $info = new AdmissionStudentInformation();
        $info->first_name = 'Bob';
        $info->last_name = 'Marley';
        $info->email = 'bob@example.com';
        $info->type_id = $seed['type']->id;
        $info->program_id = $seed['program']->id;
        $info->slug = \Str::uuid();
        $info->save();

        $uploadType = $seed['uploadTypes'][0];

        // Create file record
        $f = new AdmissionFile();
        $f->filename = '01012025010101';
        $f->orig_filename = 'orig.pdf';
        $f->filetype = 'pdf';
        $f->save();

        // Create requirement linking the file
        $sir = new StudentInformationRequirement();
        $sir->student_information_id = $info->id;
        $sir->admission_upload_type_id = $uploadType->id;
        $sir->admission_file_id = $f->id;
        $sir->save();

        // Call deleteFile with required request context
        $res = $this->delete('/api/v1/test/admissions/file/' . $f->id, [
            'student_information_id' => $info->id,
            'admission_upload_type_id' => $uploadType->id,
        ])->assertOk()->json();

        $this->assertTrue($res['success'] ?? false);
        $this->assertDatabaseMissing('admission_files', ['id' => $f->id]);
        $this->assertDatabaseMissing('student_information_requirements', ['id' => $sir->id]);
    }

    public function test_save_requirements_creates_or_updates_and_mails_when_updated(): void
    {
        Mail::fake();
        $seed = $this->seedBasics();

        $info = new AdmissionStudentInformation();
        $info->first_name = 'Cara';
        $info->last_name = 'Danvers';
        $info->email = 'cara@example.com';
        $info->type_id = $seed['type']->id;
        $info->program_id = $seed['program']->id;
        $info->slug = \Str::uuid();
        $info->save();

        // Prepare file and requirement
        $f = new AdmissionFile();
        $f->filename = '02022025020202';
        $f->orig_filename = 'file.jpg';
        $f->filetype = 'jpg';
        $f->save();

        $ut = $seed['uploadTypes'][0];

        config(['emails.admission_staging' => 'test@example.com']);
        config(['app.env' => 'local']); // ensure staging path used

        $payload = [
            'student_information_id' => $info->id,
            'requirements' => [
                [
                    'upload_type_id' => $ut->id,
                    'file_id' => $f->id,
                ],
            ],
        ];

        $res = $this->post('/api/v1/test/admissions/save-requirements', $payload)
            ->assertOk()
            ->json();

        $this->assertTrue($res['success'] ?? false);
        $this->assertDatabaseHas('student_information_requirements', [
            'student_information_id' => $info->id,
            'admission_upload_type_id' => $ut->id,
            'admission_file_id' => $f->id,
        ]);

        Mail::assertSent(SubmitRequirementsMail::class, 1);
    }

    public function test_upload_attachments_and_delete_attachment_flow(): void
    {
        Storage::fake('local');

        $seed = $this->seedBasics();

        $info = new AdmissionStudentInformation();
        $info->first_name = 'Diana';
        $info->last_name = 'Prince';
        $info->email = 'diana@example.com';
        $info->type_id = $seed['type']->id;
        $info->program_id = $seed['program']->id;
        $info->slug = \Str::uuid();
        $info->save();

        $files = [
            UploadedFile::fake()->create('a.pdf', 5, 'application/pdf'),
            UploadedFile::fake()->image('b.png', 100, 100),
        ];

        $res = $this->post('/api/v1/test/admissions/information/' . $info->id . '/attachments', [
            'files' => $files,
        ])->assertOk()->json();

        $this->assertTrue($res['success'] ?? false);
        $this->assertIsArray($res['data']);
        $this->assertCount(2, $res['data']);

        // Delete first attachment
        $firstAttachmentId = $res['data'][0]['id'];
        $res2 = $this->delete('/api/v1/test/admissions/attachment/' . $firstAttachmentId)
            ->assertOk()->json();

        $this->assertTrue($res2['success'] ?? false);
        $this->assertDatabaseMissing('acceptance_letter_attachments', ['id' => $firstAttachmentId]);
    }

    public function test_send_acceptance_mail_updates_fields_and_sends_mail(): void
    {
        Mail::fake();
        $seed = $this->seedBasics();

        $info = new AdmissionStudentInformation();
        $info->first_name = 'Eve';
        $info->last_name = 'Polastri';
        $info->email = 'eve@example.com';
        $info->type_id = $seed['type']->id;
        $info->program_id = $seed['program']->id;
        $info->slug = \Str::uuid();
        $info->save();

        $payload = [
            'content' => 'Congratulations! Welcome to iACADEMY.',
        ];

        $res = $this->post('/api/v1/test/admissions/information/' . $info->id . '/send-acceptance', $payload)
            ->assertOk()->json();

        $this->assertTrue($res['success'] ?? false);
        $this->assertEquals('For Reservation', $res['data']['status']);

        $this->assertDatabaseHas('admission_student_informations', [
            'id' => $info->id,
            'status' => 'For Reservation',
        ]);

        Mail::assertSent(SendAcceptanceLetterMail::class, 1);
    }
}

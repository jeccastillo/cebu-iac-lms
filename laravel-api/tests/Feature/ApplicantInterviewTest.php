<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicantInterviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure baseline tables exist before tests (migrations should have run)
        $this->artisan('migrate', ['--force' => true]);
        // Disable role middleware for API routes to focus on feature behavior
        $this->withoutMiddleware();
    }

    protected function seedUserAndApplicantData(): array
    {
        // Insert a minimal user row into tb_mas_users (legacy schema expects intID)
        $userId = DB::table('tb_mas_users')->insertGetId([
            'strFirstname' => 'John',
            'strLastname' => 'Doe',
            'strEmail' => 'john.doe@example.com',
            'strMobileNumber' => '09171234567',
        ], 'intID');

        // Insert corresponding applicant_data baseline row
        $applicantDataId = DB::table('tb_mas_applicant_data')->insertGetId([
            'user_id' => $userId,
            'data' => json_encode(['first_name' => 'John', 'last_name' => 'Doe']),
            'status' => 'new',
        ]);

        return [$userId, $applicantDataId];
    }

    public function test_can_schedule_interview_for_applicant_data(): void
    {
        [, $applicantDataId] = $this->seedUserAndApplicantData();

        $payload = [
            'applicant_data_id' => $applicantDataId,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'interviewer_user_id' => null,
            'remarks' => 'First schedule',
        ];

        $res = $this->postJson('/api/v1/admissions/interviews', $payload);
        $res->assertStatus(201)->assertJsonPath('success', true);

        $row = DB::table('tb_mas_applicant_interviews')->where('applicant_data_id', $applicantDataId)->first();
        $this->assertNotNull($row, 'Interview row should have been created');
        $this->assertNull($row->assessment);
        $this->assertNull($row->completed_at);
    }

    public function test_cannot_schedule_multiple_interviews_for_same_applicant_data(): void
    {
        [, $applicantDataId] = $this->seedUserAndApplicantData();

        $payload = [
            'applicant_data_id' => $applicantDataId,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ];

        $this->postJson('/api/v1/admissions/interviews', $payload)->assertStatus(201);
        $this->postJson('/api/v1/admissions/interviews', $payload)->assertStatus(422);
    }

    public function test_submit_result_sets_interviewed_flag_true(): void
    {
        [, $applicantDataId] = $this->seedUserAndApplicantData();

        $payload = [
            'applicant_data_id' => $applicantDataId,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ];
        $this->postJson('/api/v1/admissions/interviews', $payload)->assertStatus(201);

        $interviewId = DB::table('tb_mas_applicant_interviews')->where('applicant_data_id', $applicantDataId)->value('id');

        $resultPayload = [
            'assessment' => 'Passed',
            'remarks' => 'Good',
        ];
        $res = $this->putJson('/api/v1/admissions/interviews/' . $interviewId . '/result', $resultPayload);
        $res->assertStatus(200)->assertJsonPath('data.applicant_data_interviewed', true);

        $flag = DB::table('tb_mas_applicant_data')->where('id', $applicantDataId)->value('interviewed');
        $this->assertEquals(1, (int) $flag, 'interviewed flag should be true');
    }

    public function test_submit_result_requires_reason_when_failed(): void
    {
        [, $applicantDataId] = $this->seedUserAndApplicantData();

        $this->postJson('/api/v1/admissions/interviews', [
            'applicant_data_id' => $applicantDataId,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ])->assertStatus(201);

        $interviewId = DB::table('tb_mas_applicant_interviews')->where('applicant_data_id', $applicantDataId)->value('id');

        // Missing reason_for_failing must 422
        $this->putJson('/api/v1/admissions/interviews/' . $interviewId . '/result', [
            'assessment' => 'Failed',
            'remarks' => 'Weak communication',
        ])->assertStatus(422);

        // With reason_for_failing should pass
        $this->putJson('/api/v1/admissions/interviews/' . $interviewId . '/result', [
            'assessment' => 'Failed',
            'remarks' => 'Weak communication',
            'reason_for_failing' => 'Did not meet criteria',
        ])->assertStatus(200);
    }

    public function test_show_by_applicant_data_returns_404_when_absent(): void
    {
        $this->getJson('/api/v1/admissions/applicant-data/999999/interview')->assertStatus(404);
    }

    public function test_applicants_index_and_show_include_interviewed_flag(): void
    {
        [$userId, $applicantDataId] = $this->seedUserAndApplicantData();

        // Index should return interviewed default false (0)
        $res = $this->getJson('/api/v1/applicants');
        $res->assertStatus(200);
        $data = $res->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $found = collect($data)->firstWhere('id', $userId);
        $this->assertNotNull($found);
        $this->assertEquals(0, (int) ($found['interviewed'] ?? 0));

        // Flip interviewed to true and verify show
        DB::table('tb_mas_applicant_data')->where('id', $applicantDataId)->update(['interviewed' => true]);

        $resShow = $this->getJson('/api/v1/applicants/' . $userId);
        $resShow->assertStatus(200)->assertJsonPath('success', true);
        $payload = $resShow->json('data');
        $this->assertTrue((bool)($payload['interviewed'] ?? false));
    }
}

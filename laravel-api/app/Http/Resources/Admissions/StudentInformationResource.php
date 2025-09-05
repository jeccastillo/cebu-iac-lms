<?php

namespace App\Http\Resources\Admissions;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Admissions\StudentInformationRequirement;
use App\Models\Admissions\AdmissionUploadType;

class StudentInformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $uploadTypes = $this->getUploadTypes(['valid_id', 'psa', 'tor', 'passport', 'payment', 'reservation_fee']);
        $enrollmentUploadTypes = $this->getUploadTypes(['report_card', 'good_moral_certificate', 'tor', 'psa', 'waiver', 'initial_fee']);

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'email' => auth()->check() ? $this->email : '',
            'school' => auth()->check() ? $this->school : '',
            'mobile_number' => auth()->check() ? $this->mobile_number : '',
            'tel_number' => auth()->check() ? $this->tel_number : '',
            'student_type_title' => $this->studentType ? $this->studentType->title : '',
            'student_type' => $this->studentType ? $this->studentType->type : '',
            'desired_program' => $this->desiredProgram ? $this->desiredProgram->title : '',
            'type_id' => $this->studentType ? $this->studentType->id : '',
            'program_id' => $this->desiredProgram ? $this->desiredProgram->id : '',
            'upload_types' => $uploadTypes,
            'enrollment_upload_types' => $this->getEnrollmentUploadTypes()['enrollment_upload_types'],
            'enrollment_upload_types_old' => $this->getEnrollmentUploadTypes()['enrollment_upload_types_merge'],
            'status' => auth()->check() ? $this->status : '',
            'interview_remarks' => auth()->check() ? $this->interview_remarks : '',
            'acceptance_letter' => auth()->check() ? $this->acceptance_letter : '',
            'acceptance_letter_attachments' => auth()->check() ? AcceptanceAttachmentResource::collection($this->whenLoaded('acceptanceAttachments')) : '',
            'acceptance_letter_sent_date' => auth()->check() ? ($this->acceptance_letter_sent_date ? $this->acceptance_letter_sent_date->format('F d, Y') : '') : '',
            'slug' => $this->slug
        ];
    }

    // if ($this->studentType->id == 1) { //UG- Freshman
    //         $keys = ['waiver', 'psa', 'recommendation_form', 'form_128', 'initial_fee'];
    //     }

    public function getEnrollmentUploadTypes()
    {
        if ($this->studentType->id == 1) { //UG- Freshman
            $keys = ['waiver', 'psa', 'recommendation_form', 'form_128', 'initial_fee'];
        }

        if ($this->studentType->id == 2) { //UG- Transferee
            $keys = ['waiver', 'tor', 'certificate_of_transfer', 'psa', 'recommendation_form', 'id_picture', 'initial_fee'];
        }

        if ($this->studentType->id == 3) { //SHS- Freshman
            $keys = ['waiver', 'certificate_of_transfer', 'psa', 'recommendation_form', 'id_picture', 'esc_voucher', 'form_128', 'initial_fee'];
        }

        if ($this->studentType->id == 4) { //SHS- Transferee
            $keys = ['waiver', 'certificate_of_transfer', 'psa', 'recommendation_form', 'id_picture', 'form_128', 'grade_11_curriculum', 'course_description', 'esc_voucher', 'initial_fee'];
        }

        if ($this->studentType->id == 5) { //SHS- DRIVE
            $keys = ['waiver', 'psa', 'recommendation_form', 'form_128', 'initial_fee'];
        }

        if ($this->studentType->id == 7) { //2ND- DEGREE
            $keys = ['waiver', 'tor', 'certificate_of_transfer', 'psa', 'recommendation_form', 'id_picture', 'initial_fee'];
        }

        $ugOrganicKeys = ['waiver', 'id_picture'];

        $foreignStudKeys = ['waiver', 'phs', 'passport', 'i-card', 'student_visa', 'proof_of_adequate', 'scholastic_records', 'psa', 'good_moral_certificate', 'initial_fee'];

        $studentUploadTypes = $this->studentType ? AdmissionUploadTypeResource::collection(
            AdmissionUploadType::whereIn('key', $keys)->get()
        ) : collect([]);

        $ugOrganicKeysNew = [];

        foreach ($ugOrganicKeys as $key => $ugOrganicKey) {
            if (!in_array($ugOrganicKey, $keys)) {
                $ugOrganicKeysNew[] = $ugOrganicKey;
            }
        }

        $organicUploadTypes = AdmissionUploadTypeResource::collection(AdmissionUploadType::whereIn('key', $ugOrganicKeysNew)->get());

        $foreignUploadTypesNew = [];

        foreach ($foreignStudKeys as $key => $foreignUploadType) {
            if (!in_array($foreignUploadType, $keys)) {
                $foreignUploadTypesNew[] = $foreignUploadType;
            }
        }

        $foreignUploadTypes = $this->studentType ? AdmissionUploadTypeResource::collection(AdmissionUploadType::whereIn('key', $foreignUploadTypesNew)->get()) : collect([]);

        $enrollmentUploadTypes = [
            [

                'student_type' => $this->studentType->title,
                'upload_types' => count($this->getAdmissionFile($studentUploadTypes)) ? $this->getAdmissionFile($studentUploadTypes) : [],
            ],
            [
                'student_type' => 'For UG Organic',
                'upload_types' => count($this->getAdmissionFile($organicUploadTypes)) ? $this->getAdmissionFile($organicUploadTypes) : [],
            ],
            [
                'student_type' => 'For Foreign Students',
                'upload_types' => count($this->getAdmissionFile($foreignUploadTypes)) ? $this->getAdmissionFile($foreignUploadTypes) : [],
            ]
        ];

        $data['enrollment_upload_types'] = $enrollmentUploadTypes;

        $mergeUploadTypes = [];

        foreach ($enrollmentUploadTypes as $key => $enrollmentUploadType) {
            foreach ($enrollmentUploadType['upload_types'] as $key => $uploadType) {
                $mergeUploadTypes[] = $uploadType;
            }
        }

        $data['enrollment_upload_types_merge'] = $mergeUploadTypes;

        return $data;
    }

    public function getUploadTypes($keys)
    {
        $uploadTypes = collect();

        $studentUploadTypes = $this->studentType ? AdmissionUploadTypeResource::collection($this->studentType->uploadTypes()->whereIn('key', $keys)->get()) : collect([]);

        return $this->getAdmissionFile($studentUploadTypes);
    }

    public function getAdmissionFile($studentUploadTypes)
    {
        $uploadTypes = collect();

        foreach ($studentUploadTypes->sortBy('order') as $key => $studentUploadType) {
            $requirement = StudentInformationRequirement::where('student_information_id', $this->id)
                                                 ->where('admission_upload_type_id', $studentUploadType->id)
                                                 ->first();

            $file = $requirement ? ($requirement->file ? new AdmissionFileResource($requirement->file) : '') : '';

            $uploadTypes->push([
                'id' => $studentUploadType->id,
                'label' => $studentUploadType->label,
                'key' => $studentUploadType->key,
                'file' => $file,
                'order' => $studentUploadType->order,
                'is_loading' => false,
                'required' => in_array($studentUploadType->key, ['payment', 'reservation_fee']) ? true : false
            ]);
        }

        return $uploadTypes;
    }
}

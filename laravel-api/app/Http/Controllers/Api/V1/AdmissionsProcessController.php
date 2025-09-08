<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Sms\AdmissionStudentInformation;
use App\Models\Sms\AdmissionStudentType;
use App\Models\Sms\{AdmissionDesiredProgram, StudentInformationRequirement, AdmissionInterviewSchedule};
use App\Models\Sms\{AdmissionFile, StudentInfoStatusLog};
use App\Models\Sms\PaymentDetail;
use App\Models\Sms\School;
use Illuminate\Support\Facades\Validator;
use App\Mail\SubmitInformationMail;
use App\Mail\ScholarshipApplication\VerifyRequirementsMail;
use App\Http\Resources\Admissions\{StudentInformationResource, AdmissionFileResource};
use App\Mail\Admissions\{SendAcceptanceLetterMail, SubmitRequirementsMail, ForEnrollmentMail};
use App\Mail\Admissions\{ForInterviewMail, ForReservationMail, ForInterviewMakatiMail};
use App\Mail\Admissions\{AdmissionsNotificationEmail, ForEnrollmentRegistrarMail};
use App\Mail\Admissions\{SubmitInformationMakatiMail, SendAcceptanceLetterMakatiMail, ForReservationMakatiMail, ForEnrollmentMakatiMail, ForEnrollmentRegistrarMakatiMail};


use DB, Mail, App;

class AdmissionProcessController extends Controller
{
    
    //
    public function __construct(
        AdmissionStudentInformation $studentInformation,
        AdmissionInterviewSchedule $interviewSchedule,
        AdmissionStudentType $studentType,
        AdmissionDesiredProgram $desiredProgram,
        AdmissionFile $admissionFile,
        StudentInformationRequirement $studentInformationRequirement,
        PaymentDetail $paymentDetail,
        School $school
    ) {
        $this->studentInformation = $studentInformation;
        $this->studentType = $studentType;
        $this->paymentDetail = $paymentDetail;
        $this->desiredProgram = $desiredProgram;
        $this->interviewSchedule = $interviewSchedule;
        $this->admissionFile = $admissionFile;
        $this->studentInformationRequirement = $studentInformationRequirement;
        $this->school = $school;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $searchField = $request->search_field;
        $searchData = $request->search_data;
        $orderBy = $request->order_by;
        $sortField = $request->sort_field;
        $filter = $request->filter;

        $paginateCount = 10;
        if ($request->count_content) {
            $paginateCount = $request->count_content;
        }
        if($filter != "none"){
            if($request->start)
            $applications = $this->studentInformation->filterByField($searchField, $searchData)
                                    ->where('status',$filter)
                                    ->where('syid',$request->current_sem)
                                    ->where('campus', $request->campus)           
                                    ->whereNotNull($request->range_field)                         
                                    ->where($request->range_field,'>=',date("Y-m-d",strtotime($request->start)))
                                    ->where($request->range_field,'<',date("Y-m-d",strtotime($request->end)))
                                    ->orderByField($sortField, $orderBy)
                                    ->paginate($paginateCount);
            else
                $applications = $this->studentInformation->filterByField($searchField, $searchData)
                                    ->where('status',$filter)
                                    ->where('campus', $request->campus) 
                                    ->where('syid',$request->current_sem)                                    
                                    ->orderByField($sortField, $orderBy)
                                    ->paginate($paginateCount);
        }
        else
            if($request->start)
                $applications = $this->studentInformation->filterByField($searchField, $searchData)
                    ->where('syid',$request->current_sem)
                    ->where('campus', $request->campus)
                    ->whereNotNull($request->range_field)                         
                    ->where($request->range_field,'>=',date("Y-m-d",strtotime($request->start)))
                    ->where($request->range_field,'<',date("Y-m-d",strtotime($request->end)))
                    ->orderByField($sortField, $orderBy)
                    ->paginate($paginateCount);

            else
                $applications = $this->studentInformation->filterByField($searchField, $searchData)
                    ->where('syid',$request->current_sem)
                    ->where('campus', $request->campus)
                    ->orderByField($sortField, $orderBy)
                    ->paginate($paginateCount);

        if ($applications) {
            return StudentInformationResource::collection($applications);
            $data['message'] = 'Shows student application available.';
        } else {
            $data['message'] = 'No student application available';
        }
        
        return response()->json($data, 200);
    }

    public function reservedApplicants(Request $request)
    {
        //
        $searchField = $request->search_field;
        $searchData = $request->search_data;
        $orderBy = $request->order_by;
        $sortField = $request->sort_field;
        $filter = $request->filter;

        $paginateCount = 10;
        if ($request->count_content) {
            $paginateCount = $request->count_content;
        }
        
        if($request->start)
            $applications = $this->studentInformation->filterByField($searchField, $searchData)
                                    ->where(function ($query) {
                                        $query->where('status', '=', 'Reserved')
                                            ->orWhere('status', '=', 'Enlisted')
                                            ->orWhere('status', '=', 'Confirmed')
                                            ->orWhere('status', '=', 'Withdrawn Before')
                                            ->orWhere('status', '=', 'Withdrawn After')
                                            ->orWhere('status', '=', 'Withdrawn End')
                                            ->orWhere('status', '=', 'For Enrollment')
                                            ->orWhere('status', '=', 'Enrolled');
                                    })                                    
                                    ->where('syid',$request->current_sem)
                                    ->where('campus', $request->campus)           
                                    ->whereNotNull($request->range_field)                         
                                    ->where($request->range_field,'>=',date("Y-m-d",strtotime($request->start)))
                                    ->where($request->range_field,'<',date("Y-m-d",strtotime($request->end)))
                                    ->orderByField($sortField, $orderBy)
                                    ->paginate($paginateCount);
            else
                $applications = $this->studentInformation->filterByField($searchField, $searchData)   
                                    ->where(function ($query) {
                                        $query->where('status', '=', 'Reserved')
                                            ->orWhere('status', '=', 'Enlisted')
                                            ->orWhere('status', '=', 'Enrolled');
                                    })                                 
                                    ->where('campus', $request->campus) 
                                    ->where('syid',$request->current_sem)                                    
                                    ->orderByField($sortField, $orderBy)
                                    ->paginate($paginateCount);
        

        if ($applications) {
            return StudentInformationResource::collection($applications);
            $data['message'] = 'Shows student application available.';
        } else {
            $data['message'] = 'No student application available';
        }
        
        return response()->json($data, 200);
    }

    public function interviewedApplicants(Request $request)
    {
        //
        $searchField = $request->search_field;
        $searchData = $request->search_data;
        $orderBy = $request->order_by;
        $sortField = $request->sort_field;
        $filter = $request->filter;

        $paginateCount = 10;
        if ($request->count_content) {
            $paginateCount = $request->count_content;
        }
        
        $applications = $this->studentInformation->filterByField($searchField, $searchData)
                                ->where(function ($query) {
                                    $query->where('status', '=', 'Reserved')
                                        ->orWhere('status', '=', 'For Reservation')
                                        ->orWhere('status', '=', 'Confirmed')
                                        ->orWhere('status', '=', 'Withdrawn Before')
                                        ->orWhere('status', '=', 'Withdrawn After')
                                        ->orWhere('status', '=', 'Withdrawn End')
                                        ->orWhere('status', '=', 'Rejected')
                                        ->orWhere('status', '=', 'For Enrollment')
                                        ->orWhere('status', '=', 'Did Not Reserve')
                                        ->orWhere('status', '=', 'Enlisted')
                                        ->orWhere('status', '=', 'Enrolled');
                                })                                    
                                ->where('syid',$request->current_sem)
                                ->where('campus', $request->campus)                                                                                                      
                                ->orderByField($sortField, $orderBy)
                                ->paginate($paginateCount);
           
        

        if ($applications) {
            return StudentInformationResource::collection($applications);
            $data['message'] = 'Shows student application available.';
        } else {
            $data['message'] = 'No student application available';
        }
        
        return response()->json($data, 200);
    }

    
    public function applicantsPaidAppFee(Request $request)
    {
        $searchField = $request->search_field;
        $searchData = $request->search_data;
        $orderBy = $request->order_by;
        $sortField = $request->sort_field;
        $filter = $request->filter;

        $paginateCount = 10;
        if ($request->count_content) {
            $paginateCount = $request->count_content;
        }
        if($filter != "none"){
            if($request->start)
            $applications = $this->studentInformation->filterByField($searchField, $searchData)
                                    ->withAndWhereHas('payments', function ($query){
                                        $query->where('status', 'Paid')
                                                ->where('description', 'Application Payment');
                                    })
                                    ->where('status',$filter)
                                    ->where('syid',$request->current_sem)
                                    ->where('campus', $request->campus)
                                    ->whereNotNull($request->range_field)                         
                                    ->where($request->range_field,'>=',date("Y-m-d",strtotime($request->start)))
                                    ->where($request->range_field,'<',date("Y-m-d",strtotime($request->end)))
                                    ->orderByField($sortField, $orderBy)
                                    ->paginate($paginateCount);
            else
                $applications = $this->studentInformation->filterByField($searchField, $searchData)
                                    ->withAndWhereHas('payments', function ($query){
                                        $query->where('status', 'Paid')
                                                ->where('description', 'Application Payment');
                                    })
                                    ->where('status',$filter)
                                    ->where('campus', $request->campus) 
                                    ->where('syid',$request->current_sem)                                    
                                    ->orderByField($sortField, $orderBy)
                                    ->paginate($paginateCount);
        }
        else
            if($request->start)
                $applications = $this->studentInformation->filterByField($searchField, $searchData)
                    ->withAndWhereHas('payments', function ($query){
                        $query->where('status', 'Paid')
                                ->where('description', 'Application Payment');
                    })
                    ->where('syid',$request->current_sem)
                    ->where('campus', $request->campus)
                    ->whereNotNull($request->range_field)                         
                    ->where($request->range_field,'>=',date("Y-m-d",strtotime($request->start)))
                    ->where($request->range_field,'<',date("Y-m-d",strtotime($request->end)))
                    ->orderByField($sortField, $orderBy)
                    ->paginate($paginateCount);

            else
                $applications = $this->studentInformation->filterByField($searchField, $searchData)
                    ->withAndWhereHas('payments', function ($query){
                        $query->where('status', 'Paid')
                                ->where('description', 'Application Payment');
                    })
                    ->where('syid',$request->current_sem)
                    ->where('campus', $request->campus)
                    ->orderByField($sortField, $orderBy)
                    ->paginate($paginateCount);

        if ($applications) {
            return StudentInformationResource::collection($applications);
            $data['message'] = 'Shows student application available.';
        } else {
            $data['message'] = 'No student application available';
        }
        
        return response()->json($data, 200);
    }

    public function awarenessStats(Request $request){
        $data['applications'] = $this->studentInformation   
        ->select(DB::raw('count(source) as count, source'))
        ->whereRaw("syid = '".$request->current_sem."' AND campus = '".$request->campus."'")        
        ->groupBy('source')
        ->get();
        
        return $data;
    }
    public function enrollmentStats(Request $request)
    {
        
        $applications = $this->studentInformation   
        ->select(DB::raw('count(*) as reserved_count, type_id, program, student_type'))
        ->whereRaw("syid = '".$request->current_sem."' AND campus = '".$request->campus."' AND (status = 'Reserved' OR status = 'For Enrollment' OR status = 'Confirmed' OR status = 'Enlisted')")        
        ->groupBy('type_id')
        ->get();

        $data = [];

        foreach($applications as $app){
            $data[$app->type_id] = $this->studentInformation   
            ->select(DB::raw('count(*) as reserved_count, type_id, program, student_type'))
            ->whereRaw("syid = '".$request->current_sem."' AND campus = '".$request->campus."' AND type_id = '".$app->type_id."' AND (status = 'Reserved' OR status = 'For Enrollment' OR status = 'Confirmed' OR status = 'Enlisted')")        
            ->groupBy('student_type')
            ->get();
        }

    

        if ($applications) {
            return $data;            
        } else {
            $data['message'] = 'No student application available';
        }
        
        return response()->json($data, 200);
    }

    public function enrolledApplicants(Request $request){
       
        if($request->start)
        $data['data'] = $this->studentInformation           
            ->where('syid',$request->current_sem)        
            ->where('status','Enrolled')
            ->whereNotNull('date_enrolled')
            ->where('date_enrolled','>=',$request->start)
            ->where('date_enrolled','<=',$request->end)
            ->get();
        else
        $data['data'] = $this->studentInformation           
            ->where('syid',$request->current_sem)        
            ->where('status','Enrolled')
            ->where('date_enrolled',date("Y-m-d"))
            ->get();

        return response()->json($data, 200);

    }    
    public function admissionStats(Request $request){

        if($request->start){
            $data['new'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','New')
                ->where('campus',$request->campus)
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->count();

            $data['floating'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Floating')
                ->where('campus',$request->campus)
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->count();

            $data['will_not_proceed'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Will Not Proceed')
                ->where('campus',$request->campus)
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->count();
            
            $students = $this->studentInformation           
                ->where('syid',$request->current_sem)                        
                ->where('campus',$request->campus)
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->get();
        
        }
        else{
            $data['new'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','New')
                ->count();
            
            $data['floating'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Floating')
                ->count();

            $data['will_not_proceed'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Will Not Proceed')
                ->count();

            $students = $this->studentInformation 
                ->where('campus',$request->campus)          
                ->where('syid',$request->current_sem)                        
                ->get();
        }

        $paid = 0;
        $unpaid = 0;
        foreach($students as $student){
            if($this->paymentDetail::where('student_information_id', $student->id)
                                    ->where('status','Paid')                                    
                                    ->where('description','Application Payment')
                                    ->where('sy_reference',$request->current_sem)
                                    ->first()){
                                        $paid += 1;

            }
            else{
                $unpaid += 1;
            }
        }

        $data['paid'] = $paid;
        $data['unpaid'] = $unpaid;        
        
        if($request->start)
            $data['waiting'] = $this->studentInformation           
                ->where('syid',$request->current_sem)  
                ->where('campus',$request->campus)      
                ->where('status','Waiting for Interview')
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->count();
        else
            $data['waiting'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Waiting for Interview') 
                ->count();

        if($request->start)
            $data['for_interview'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','For Interview')
                ->where('campus',$request->campus)
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->count();
        else
            $data['for_interview'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','For Interview')
                ->count();

        if($request->start)
            $data['for_reservation'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','For Reservation')
                ->whereNotNull('date_interviewed')
                ->where('campus',$request->campus)
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        else
            $data['for_reservation'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','For Reservation')
                ->count();
               
        
        if($request->start)
            $reserved_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Reserved')
                ->whereNotNull('date_reserved')
                ->where('campus',$request->campus)
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        else
            $reserved_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Reserved')
                ->where('campus',$request->campus)
                ->count();                
        
        if($request->start)
            $dnr_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Did Not Reserve')
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        else
            $dnr_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Did Not Reserve')
                ->count();
        if($request->start)
            $rejected_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Rejected')
                ->where('campus',$request->campus)
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        else
            $rejected_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Rejected')
                ->count();


        if($request->start)
            $fe_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','For Enrollment')
                ->where('campus',$request->campus)
                ->whereNotNull('date_reserved')
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        else
            $fe_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','For Enrollment')
                ->count();

        if($request->start)
            $cf_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Confirmed')
                ->where('campus',$request->campus)
                ->whereNotNull('date_reserved')
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        else
            $cf_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Confirmed')
                ->count();

        if($request->start)
            $enlisted_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Enlisted')
                ->where('campus',$request->campus)
                ->whereNotNull('date_reserved')
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        else
            $enlisted_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Enlisted')
                ->count();

        if($request->start)
            $enrolled_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Enrolled')
                ->where('campus',$request->campus)
                ->whereNotNull('date_enrolled')
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        else
            $enrolled_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Enrolled')
                ->count();

        


        $data['interviewed'] = $data['for_reservation'] + $reserved_interviewed + $dnr_interviewed + $rejected_interviewed + $enlisted_interviewed + $cf_interviewed + $fe_interviewed + $enrolled_interviewed;


        if($request->start)
            $data['reserved'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Reserved')
                ->whereNotNull('date_reserved')
                ->where('campus',$request->campus)
                ->where('date_reserved','>=',$request->start)
                ->where('date_reserved','<=',$request->end)
                ->count();
        else
            $data['reserved'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Reserved')
                ->where('campus',$request->campus)
                ->count();

        if($request->start)
            $data['for_enrollment'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','For Enrollment')
                ->where('campus',$request->campus)
                ->whereNotNull('date_reserved')
                ->where('date_reserved','>=',$request->start)
                ->where('date_reserved','<=',$request->end)
                ->count();
        else
            $data['for_enrollment'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','For Enrollment')
                ->count();

        if($request->start)
            $data['confirmed'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Confirmed')
                ->where('campus',$request->campus)
                ->whereNotNull('date_reserved')
                ->where('date_reserved','>=',$request->start)
                ->where('date_reserved','<=',$request->end)
                ->count();
        else
            $data['confirmed'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Confirmed')
                ->count();

        if($request->start)
            $data['enlisted'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Enlisted')
                ->where('campus',$request->campus)
                ->whereNotNull('date_reserved')
                ->where('date_reserved','>=',$request->start)
                ->where('date_reserved','<=',$request->end)
                ->count();
        else
            $data['enlisted'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Enlisted')
                ->count();

        if($request->start)
            $data['enrolled'] = $this->studentInformation           
                ->where('syid',$request->current_sem)                        
                ->where(function ($query) {
                    $query->where('status', '=', 'Enrolled')                        
                        ->orWhere('status', '=', 'Withdrawn After')
                        ->orWhere('status', '=', 'Withdrawn End');
                })   
                ->where('campus',$request->campus)
                ->whereNotNull('date_enrolled')
                ->where('date_enrolled','>=',$request->start)
                ->where('date_enrolled','<=',$request->end)
                ->count();
        else
            $data['enrolled'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where(function ($query) {
                    $query->where('status', '=', 'Enrolled')                        
                        ->orWhere('status', '=', 'Withdrawn After')
                        ->orWhere('status', '=', 'Withdrawn End');
                })   
                ->count();

        if($request->start)
            $data['enrolled_reserved'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Enrolled')
                ->where('campus',$request->campus)
                ->whereNotNull('date_enrolled')
                ->where('date_reserved','>=',$request->start)
                ->where('date_reserved','<=',$request->end)
                ->count();
        else
            $data['enrolled_reserved'] = $data['enrolled'];
                 
        $data['reserved_count'] = $data['reserved'];
        

        if($request->start)
            $data['rejected'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('status','Rejected')
                ->where('campus',$request->campus)
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->count();
        else
            $data['rejected'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Rejected')
                ->count();

        if($request->start)
            $data['cancelled'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Cancelled')
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->count();
        else
            $data['cancelled'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Cancelled')
                ->count();
        
        if($request->start)
            $data['did_not_reserve'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Did Not Reserve')
                ->where('created_at','>=',$request->start)
                ->where('created_at','<=',$request->end)
                ->count();
        else
            $data['did_not_reserve'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Did Not Reserve')
                ->count();

        if($request->start){
            $data['withdrawn_before'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn Before')
                ->where('date_withdrawn','>=',$request->start)
                ->where('date_withdrawn','<=',$request->end)
                ->count();

            $wb_reserved = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn Before')
                ->where('date_reserved','>=',$request->start)
                ->where('date_reserved','<=',$request->end)
                ->count();
            
            $wb_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn Before')
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        }
        else{
            $data['withdrawn_before'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn Before')
                ->count();
            
            $wb_reserved = $wb_interviewed = $data['withdrawn_before'];

        }

        if($request->start){
            $data['withdrawn_after'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn After')
                ->where('date_withdrawn','>=',$request->start)
                ->where('date_withdrawn','<=',$request->end)
                ->count();
            $wa_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn After')
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
            $wa_reserved = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn After')
                ->where('date_reserved','>=',$request->start)
                ->where('date_reserved','<=',$request->end)
                ->count();
        }
        else{
            $data['withdrawn_after'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn After')
                ->count();
            $wa_reserved = $wa_interviewed = $data['withdrawn_after'];
        }
        
        if($request->start){
            $data['withdrawn_end'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn End')
                ->where('date_withdrawn','>=',$request->start)
                ->where('date_withdrawn','<=',$request->end)
                ->count();
            $we_reserved = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn End')
                ->where('date_reserved','>=',$request->start)
                ->where('date_reserved','<=',$request->end)
                ->count();
            $we_interviewed = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn End')
                ->where('date_interviewed','>=',$request->start)
                ->where('date_interviewed','<=',$request->end)
                ->count();
        }
        else{
            $data['withdrawn_end'] = $this->studentInformation           
                ->where('syid',$request->current_sem)        
                ->where('campus',$request->campus)
                ->where('status','Withdrawn End')
                ->count();
            $we_reserved = $we_interviewed = $data['withdrawn_end'];
                
        }


        $data['reserved'] +=  $data['enrolled_reserved'] +  $data['enlisted'] + $data['confirmed'] + $data['for_enrollment'] + $we_reserved + $wb_reserved + $wa_reserved;
        $data['interviewed'] += $we_interviewed + $wa_interviewed + $wb_interviewed;

        return response()->json($data, 200);

    }

    public function delete($slug){

        $studentInformation = $this->studentInformation
                                    ->where('slug', $slug)
                                    ->first();

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }
        elseif($studentInformation->status == "New"){
            $studentInformation->delete();
            $data['message'] = 'Successfully deleted.';
            $data['success'] = true;   
        }
        else{
            $data['message'] = 'Can not delete status needs to be new.';
            $data['success'] = false;   
        }
                
             
        return response()->json($data);

    }
    public function getStudentTypes()
    {
        return response()->json([
            'data' => $this->studentType->select('id', 'title', 'type')->get()            
        ]);
    }

    public function getDesiredPrograms()
    {
        return response()->json([
            'data' => $this->desiredProgram->where('type', '!=', 'others')->select('id', 'title', 'type')->get()
        ]);
    }

    public function storeInformation()
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make(request()->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|confirmed|email'
            ]); //|unique:admission_student_information,email

            if ($validator->fails()) {
                if (isset($validator->messages()->toArray()['first_name'])) {
                    $message[] = $validator->messages()->toArray()['first_name'][0];
                }

                if (isset($validator->messages()->toArray()['last_name'])) {
                    $message[] = $validator->messages()->toArray()['last_name'][0];
                }

                if (isset($validator->messages()->toArray()['email'])) {
                    $message[] = $validator->messages()->toArray()['email'][0];
                }

                $data['success'] = false;
                $data['response'] = $validator->messages();
                $data['message'] = str_replace('.', '', implode(', ', $message));
                return response()->json($data);
            }

            $emailExists = $this->studentInformation::where('email', request('email'))
                                                       ->first();

            if($emailExists){

                $data['success'] = false;
                $data['response'] = "The email address ".request('email')." is already in use";
                $data['message'] = "The email address ".request('email')." is already in use";
                return response()->json($data);

            }
            
            $studentInformation = new $this->studentInformation();
            $studentInformation->first_name = request('first_name');
            $studentInformation->last_name = request('last_name');
            $studentInformation->middle_name = request('middle_name');
            $studentInformation->suffix = request('suffix');
            $studentInformation->email = request('email');
            $studentInformation->school = request('school');
            $studentInformation->mobile_number = request('mobile_number');
            $studentInformation->tel_number = request('tel_number');
            $studentInformation->type_id = request('type_id');
            $studentInformation->type_id2 = request('type_id2');
            $studentInformation->type_id3 = request('type_id3');
            $studentInformation->program = request('program');
            $studentInformation->program2 = request('program2');
            $studentInformation->program3 = request('program3');
            $studentInformation->address = request('address');
            $studentInformation->syid = request('syid');
            $studentInformation->student_type = request('student_type');
            $studentInformation->status = 'New';            
            $studentInformation->date_of_birth = request('date_of_birth');
            $studentInformation->place_of_birth = request('place_of_birth');
            $studentInformation->gender = request('gender');
            $studentInformation->source = request('source');
            $studentInformation->referrer = request('referrer');
            $studentInformation->good_moral = request('good_moral');
            $studentInformation->crime = request('crime');
            $studentInformation->citizenship = request('citizenship');
            $studentInformation->hospitalized = request('hospitalized');
            $studentInformation->hospitalized_reason = request('hospitalized_reason');
            $studentInformation->health_concern = request('health_concern');
            $studentInformation->other_health_concern = request('other_health_concern');
            $studentInformation->father_name = request('father_name')?request('father_name'):"no info";
            $studentInformation->father_contact = request('father_contact')?request('father_contact'):"no info";
            $studentInformation->father_email = request('father_email')?request('father_email'):"no info";
            $studentInformation->father_occupation = request('father_occupation')?request('father_occupation'):"no info";
            $studentInformation->mother_name = request('mother_name')?request('mother_name'):"no info";
            $studentInformation->mother_contact = request('mother_contact')?request('mother_contact'):"no info";
            $studentInformation->mother_email = request('mother_email')?request('mother_email'):"no info";
            $studentInformation->mother_occupation = request('mother_occupation')?request('mother_occupation'):"no info";
            $studentInformation->guardian_name = request('guardian_name')?request('guardian_name'):"no info";
            $studentInformation->guardian_contact = request('guardian_contact')?request('guardian_contact'):"no info";
            $studentInformation->guardian_email = request('guardian_email')?request('guardian_email'):"no info";
            $studentInformation->guardian_occupation = request('guardian_occupation')?request('guardian_occupation'):"no info";
            $studentInformation->primary_contact = request('primary_contact')?request('primary_contact'):"no info";
            $studentInformation->campus = request('campus')?request('campus'):"Cebu";
            $studentInformation->type = request('type')?request('type'):'college';
            $studentInformation->sd_company = request('sd_company');
            $studentInformation->sd_position = request('sd_position');
            $studentInformation->sd_degree = request('sd_degree');
            $studentInformation->barangay = request('barangay');
            $studentInformation->city = request('city');
            $studentInformation->province = request('province');
            $studentInformation->country = request('country');
            $studentInformation->country_of_citizenship2 = request('country_of_citizenship2');
            $studentInformation->grade_year_level = request('grade_year_level');
            $studentInformation->program_strand_degree = request('program_strand_degree');
            $studentInformation->best_time = request('best_time');
            $studentInformation->schedule_date = request('schedule_date');
            $studentInformation->schedule_time_from = request('schedule_time_from');
            $studentInformation->schedule_time_to = request('schedule_time_to');
            $studentInformation->program_school = request('program_school');
            $studentInformation->year_start = request('year_start');

            if(request('school_id')){
                $studentInformation->school_id = request('school_id');
            }else if(request('school_name')){
                //check for duplicate school name
                $checkSchool = $this->school->where('name', request('school_name'))->first();

                if($checkSchool){
                    $data['message'] = 'School Name already existed';
                    $data['success'] = false;
                    $data['data'] = null;

                    return response()->json($data);
                }else{
                    $newSchool = new $this->school();
                    $newSchool->name = request('school_name');
                    $newSchool->city = request('school_city');
                    $newSchool->province = request('school_province');
                    $newSchool->country = request('school_country');
                    $newSchool->save();

                    $studentInformation->school_id = $newSchool->id;
                }
            }
            
            $studentInformation->slug = \Str::uuid();
            $studentInformation->save();

            if($studentInformation->campus == 'Cebu'){
                // Email registrant
                Mail::to($studentInformation)->send(
                    new SubmitInformationMail($studentInformation)
                );
            }else if($studentInformation->campus == 'Makati'){
                Mail::to($studentInformation)->send(
                    new SubmitInformationMakatiMail($studentInformation)
                );
            }

            $data['message'] = 'Success! Please check your email for the next step.';
            $data['success'] = true;
            $data['data'] = new StudentInformationResource($studentInformation);
            DB::commit();
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['response'] = $e->getMessage();
            $data['message'] = 'Server Error.';
            DB::rollback();
            return response()->json($data);
        }
    }

    public function viewInformation($slug)
    {
        $studentInformation = $this->studentInformation::with('acceptanceAttachments')
                                                       ->where('id', $slug)
                                                       ->where('email', request('email'))
                                                       ->first();
        if (!$studentInformation) {
            $studentInformation = $this->studentInformation::with('acceptanceAttachments')
                                                       ->where('slug', $slug)
                                                       ->first();
        }

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $data['success'] = true;
        $data['data'] = new StudentInformationResource($studentInformation);
        return response()->json($data);
    }

    public function viewStudents($sem)
    {
        $studentInformation = $this->studentInformation->select('admission_student_information.*', 'schools.name as school_name', 'admission_application_call_logs.call_status as call_status', 'admission_application_call_logs.remarks as call_remarks')
                                ->where('admission_student_information.syid', $sem)
                                ->leftJoin('schools', 'admission_student_information.school_id', '=', 'schools.id')
                                ->leftJoin('admission_application_call_logs', 'admission_student_information.id', '=', 'admission_application_call_logs.admission_student_information_id')
                                ->whereNotIn('admission_student_information.status', ['Cancelled', 'Will Not Proceed', 'Did Not Reserve'])
                                ->groupBy('admission_student_information.slug')
                                ->get();

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $data['success'] = true;
        $data['data'] = $studentInformation;
        return response()->json($data);
    }

    public function viewApplicants($sem, $campus)
    {
        $studentInformation = $this->studentInformation->select('admission_student_information.*', 'schools.name as school_name', 'admission_application_call_logs.call_status as call_status', 'admission_application_call_logs.remarks as call_remarks')
                                ->where('admission_student_information.syid', $sem)
                                ->where('campus', $campus)
                                ->leftJoin('schools', 'admission_student_information.school_id', '=', 'schools.id')
                                ->leftJoin('admission_application_call_logs', 'admission_student_information.id', '=', 'admission_application_call_logs.admission_student_information_id')
                                ->whereNotIn('admission_student_information.status', ['Cancelled', 'Will Not Proceed', 'Did Not Reserve'])
                                ->groupBy('admission_student_information.slug')
                                ->orderBy('created_at', 'asc')
                                ->get();

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $data['success'] = true;
        $data['data'] = $studentInformation;
        return response()->json($data);
    }

    public function viewInformationForAdmission($slug)
    {
        $studentInformation = $this->studentInformation::with('acceptanceAttachments')
                                                       ->where('id', $slug)
                                                       ->where('email', request('email'))
                                                       ->first();
        if (!$studentInformation) {
            $studentInformation = $this->studentInformation::with('acceptanceAttachments')
                                                       ->where('slug', $slug)
                                                       ->first();
        }

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $data['success'] = true;
        $data['data'] = new StudentInformationResource($studentInformation);
        return response()->json($data);
    }    

    public function uploadRequirements()
    {
        $validator = Validator::make(request()->all(), [
            'file' => 'required|max:500000|mimes:jpg,png,gif,pdf,jpeg,docx'
        ]);

        if ($validator->fails()) {
            if (isset($validator->messages()->toArray()['file'])) {
                $message[] = str_replace('.', '', implode(', ', $validator->messages()->toArray()['file']));
            }

            $data['success'] = false;
            $data['response'] = $validator->messages();
            $data['message'] = str_replace('.', '', implode(', ', $message));
            return response()->json($data);
        }


        //check exist in requirements
        $checkExist = $this->admissionFile->where('type', request('type'))
                                          ->where('slug', request('slug')) 
                                          ->first();

        // if ($response->successful()) {
        //     return response()->json([
        //         'url' => $response['data']['url'],
        //         'display_url' => $response['data']['display_url'],
        //         'delete_url' => $response['data']['delete_url'],
        //     ]);
        // }

        $path = '';
        $filenameWithExt = request()->file('file')->getClientOriginalName();
        $filename = now()->format('dmYHis');
        $extension = request()->file('file')->getClientOriginalExtension();
        $fileNameToStore = $filename . '.' . $extension;

        if(request('type') == '2x2' || request('type') == '2x2_foreign'){
            // Read and encode image
            $imagePath = request()->file('file')->getRealPath();
            $imageData = base64_encode(file_get_contents($imagePath));
    
            $response = Http::asForm()->post('https://api.imgbb.com/1/upload', [
                'key' => '2cfd06620d611ad28130e951f5362664',
                'image' => $imageData,
            ]);

            $path = $response['data']['display_url'];

        }else{
            $path = request()->file('file')->storeAs('public/admission_files', $fileNameToStore);
            $path = url('storage/admission_files/'.$filename.'.'.$extension);
        }

        if (!$checkExist) {
            $file = new $this->admissionFile();
            $file->filename = $filename;
            $file->type = request('type');
            $file->slug = request('slug');
            $file->orig_filename =  $filenameWithExt;
            $file->filetype = $extension;
            // $file->path = url('storage/admission_files/'.$filename.'.'.$extension);
            $file->path = $path;
            $file->save();

            $data['data'] = new AdmissionFileResource($file);
        } else {
            $checkExist->orig_filename =  $filenameWithExt;
            $checkExist->filename = $filename;
            $checkExist->filetype = $extension;
            $checkExist->path = $path;
            $checkExist->update();

            $data['data'] = new AdmissionFileResource($checkExist);
        }

        $data['message'] = 'Successfully uploaded';
        $data['success'] = true;
        return response()->json($data);
    }

    public function updateRequirements(){

        $validator = Validator::make(request()->all(), [
            'file' => 'required|max:500000|mimes:jpg,png,gif,pdf,jpeg,docx'
        ]);

        if ($validator->fails()) {
            if (isset($validator->messages()->toArray()['file'])) {
                $message[] = str_replace('.', '', implode(', ', $validator->messages()->toArray()['file']));
            }

            $data['success'] = false;
            $data['response'] = $validator->messages();
            $data['message'] = str_replace('.', '', implode(', ', $message));
            return response()->json($data);
        }


        $studentInformation = $this->studentInformation->where('slug', request('slug'))->first();

        //check exist in requirements
        $file = $this->admissionFile->where('type', request('type'))
                                          ->where('slug', request('slug')) 
                                          ->first();

        $path = '';
        $filenameWithExt = request()->file('file')->getClientOriginalName();
        $filename = now()->format('dmYHis');
        $extension = request()->file('file')->getClientOriginalExtension();
        $fileNameToStore = $filename . '.' . $extension;

        if(request('type') == '2x2' || request('type') == '2x2_foreign'){
            // Read and encode image
            $imagePath = request()->file('file')->getRealPath();
            $imageData = base64_encode(file_get_contents($imagePath));
    
            $response = Http::asForm()->post('https://api.imgbb.com/1/upload', [
                'key' => '2cfd06620d611ad28130e951f5362664',
                'image' => $imageData,
            ]);

            $path = $response['data']['display_url'];

        }else{
            $path = request()->file('file')->storeAs('public/admission_files', $fileNameToStore);
            $path = url('storage/admission_files/'.$filename.'.'.$extension);      
        }
        
        if (!$file) {
            $file = new $this->admissionFile();
            $file->filename = $filename;
            $file->type = request('type');
            $file->slug = request('slug');
            $file->orig_filename =  $filenameWithExt;
            $file->filetype = $extension;
            $file->path = $path;
            $file->save();            

            $data['data'] = new AdmissionFileResource($file);
        } else {
            $file->orig_filename =  $filenameWithExt;
            $file->filename = $filename;
            $file->filetype = $extension;
            $file->path = $path;
            $file->update();

            $data['data'] = new AdmissionFileResource($file);
        }

        $checkExist = $this->studentInformationRequirement->where('student_information_id', $studentInformation->id)
                                                            ->where('admission_file_id', $file->id)
                                                            ->first();
        if (!$checkExist) {
            $req = new $this->studentInformationRequirement;
            $req->student_information_id = $studentInformation->id;
            $req->admission_file_id = $file->id;
            $req->save();
        }

        $data['message'] = 'Successfully uploaded';
        $data['success'] = true;
        return response()->json($data);
    }

    public function deleteFile($id)
    {
        $file = $this->admissionFile->find($id);

        if (!$file) {
            $data['success'] = false;
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        \Storage::delete('public/admission_files/' . $file->filename . '.' . $file->filetype);

        $req = $this->studentInformationRequirement
                    ->where('student_information_id', request('student_information_id'))
                    ->where('admission_upload_type_id', request('admission_upload_type_id'))
                    ->where('admission_file_id', $id)
                    ->first();
        if ($req) {
            $req->delete();
        }

        $file->delete();

        $data['success'] = true;
        $data['message'] = 'Successfully deleted!';
        return response()->json($data);
    }

    public function saveRequirements()
    {
        $studentInformation = $this->studentInformation->where('slug', request('slug'))->first();

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $updatedFields = [];

        foreach (request('requirements') as $key => $requirement) {

            $checkExist = $this->studentInformationRequirement->where('student_information_id', $studentInformation->id)
                                                              ->where('admission_file_id', $requirement['file_id'])
                                                              ->first();
            if (!$checkExist) {
                $req = new $this->studentInformationRequirement;
                $req->student_information_id = $studentInformation->id;
                $req->admission_file_id = $requirement['file_id'];
                $req->save();
            }
        }
        if (App::environment(['local', 'staging'])) 
            $toEmail = "rms@iacademy.edu.ph";
        else{
            if($studentInformation->campus == 'Makati')
                $toEmail = config('emails.admission_makati.email');
            else
                $toEmail = config('emails.admission.email');
        }

        if (count(request('requirements'))) {
           //Email admissions
            Mail::to($toEmail)->send(
                new SubmitRequirementsMail($studentInformation, $updatedFields)
            );
        }

        $data['message'] = 'Successfully submitted';
        $data['success'] = true;
        $data['data'] = new StudentInformationResource($studentInformation);
        return response()->json($data);
    }

    public function updateInformation($id)
    {
        $studentInformation = $this->studentInformation->find($id);

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $studentInformation->type_id = request('type_id');
        $studentInformation->program_id = request('program_id');
        $studentInformation->update();

        $data['message'] = 'Successfully updated.';
        $data['success'] = true;
        $data['data'] = new StudentInformationResource($studentInformation);
        return response()->json($data);
    }

    public function syncEnrollmentDates(){
        $registrations = request('registrations');

        foreach($registrations as $registration){
            $studentInformation = $this->studentInformation->where('slug', $registration['slug'])->first();    
            $studentInformation->date_enrolled = date("Y-m-d",strtotime($registration['dteRegistered']));
            $studentInformation->update();
        }

        $data['message'] = 'Successfully updated.';
        $data['success'] = true;        
        return response()->json($data);
    }

    public function updateInformationStatus($slug)
    {
        $studentInformation = $this->studentInformation->where('slug', $slug)->first();

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        if (request('program_id')) {
            $studentInformation->program_id = request('program_id');
        }

        if (App::environment(['local', 'staging'])){ 
            if($studentInformation->campus == "Cebu")
                $url = 'http://172.16.80.26/';          
            else
                $url = 'http://172.16.80.26/makati-dev/';
        }              
        else{
            if($studentInformation->campus == "Cebu")
                $url = 'http://172.16.80.38/';
            else
                $url = 'http://172.16.80.38/makati_sms/';
        }
        
        $studentInformation->status = request('status');
        
        if(request('status') == "Enrolled" && $studentInformation->date_enrolled == null)
            $studentInformation->date_enrolled = date("Y-m-d");
        
        StudentInfoStatusLog::storeLogs($studentInformation->id, request('status'), request('admissions_officer'), request('remarks'));

        if (App::environment(['local', 'staging'])) 
                $regEmail = "rms@iacademy.edu.ph";
            else{
                if($studentInformation->campus == 'Makati')
                    $regEmail = "registrar@iacademy.edu.ph";
                else
                    $regEmail = "registrarcebu@iacademy.edu.ph";

            }

        if (request('status') == 'For Interview') {
            if($studentInformation->campus == 'Makati'){
                Mail::to($studentInformation->email)->send(
                    new ForInterviewMakatiMail($studentInformation)
                );
            }else if($studentInformation->campus == 'Cebu'){
                Mail::to($studentInformation->email)->send(
                    new ForInterviewMail($studentInformation)
                );
            }
        } else if (request('status') == 'Withdrawn Before' || request('status') == 'Withdrawn After' || request('status') == 'Withdrawn End') {             
                            
            $studentInformation->date_withdrawn = date("Y-m-d");
            
        }else if (request('status') == 'For Reservation') {             
                
            $response = Http::get($url.'unity/get_active_sem/'.$studentInformation->syid);              

            $dataResp = $response->body();            
            $dataResp = json_decode($dataResp);            
            
            $sched = $this->interviewSchedule->where('student_information_id',$studentInformation->id)->first();
            if(!$sched){
                $data['success'] = false;
                $data['data'] = [];
                $data['message'] = 'This applicant does not have an interview schedule can not proceed with update';
                return response()->json($data);
            }
            $studentInformation->date_interviewed = $sched->date;

            if($studentInformation->campus == 'Makati'){
                Mail::to($studentInformation->email)
                ->bcc(['admissions@iacademy.edu.ph'])
                ->send(
                    new ForReservationMakatiMail($studentInformation, $dataResp->active_sem)
                );  
            }else{
                Mail::to($studentInformation->email)
                ->bcc(['admissionscebu@iacademy.edu.ph'])
                ->send(
                    new ForReservationMail($studentInformation, $dataResp->active_sem)
                );                  
            }
        }else if (request('status') == 'For Enrollment') {                        

            $response = Http::asForm()->post($url.'admissionsV1/add_new_student', [
                'strFirstname' => $studentInformation->first_name,
                'strLastname' => $studentInformation->last_name,
                'strMiddlename' => $studentInformation->middle_name,
                'strEmail' => $studentInformation->email,
                'dteBirthDate' => $studentInformation->date_of_birth,
                'strAddress' => $studentInformation->address,
                'intProgramID' => $studentInformation->type_id,
                'slug' => $studentInformation->slug,
                'strMobileNumber' => $studentInformation->mobile_number,
                'strTelNumber' => $studentInformation->tel_number,
                'strCitizenship' => $studentInformation->citizenship,                
                'father' => @$studentInformation->father_name,
                'father_email' => @$studentInformation->father_email,
                'father_contact' => @$studentInformation->father_contact,
                'mother' => @$studentInformation->mother_name,
                'mother_email' => @$studentInformation->mother_email,
                'mother_contact' => @$studentInformation->mother_contact,
                'guardian' => @$studentInformation->guardian_name,
                'guardian_email' => @$studentInformation->guardian_email,
                'guardian_contact' => @$studentInformation->guardian_contact,
                'level'=> $studentInformation->type?$studentInformation->type:'college',
                'intCurriculumID' => 1,
            ]);

            $data['response'] = $response->body();
        
            if($studentInformation->campus == 'Cebu'){
                Mail::to($studentInformation->email)->send(
                    new ForEnrollmentMail($studentInformation)
                );
            }else{
                Mail::to($studentInformation->email)->send(
                    new ForEnrollmentMakatiMail($studentInformation)
                );
            }

            
        }else if (request('status') == 'Confirmed') { 
            //Registrar Email here
            if($studentInformation->campus == 'Makati'){
                Mail::to($regEmail)->send(
                    new ForEnrollmentRegistrarMakatiMail($studentInformation)
                );
            }else{
                Mail::to($regEmail)->send(
                    new ForEnrollmentRegistrarMail($studentInformation)
                );
            }
        }

        if(request('voucher')){
            $studentInformation->voucher = hp()->uploadFile('admission_files/voucher', request('voucher'));
        }
        $studentInformation->update();

        $data['message'] = 'Successfully updated.';
        $data['success'] = true;
        $data['data'] = new StudentInformationResource($studentInformation);
        return response()->json($data);
    }


    public function updateInformationRemarks($id)
    {
        $studentInformation = $this->studentInformation->find($id);

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $studentInformation->interview_remarks = request('interview_remarks');
        $studentInformation->update();

        $data['message'] = 'Successfully updated.';
        $data['success'] = true;
        $data['data'] = new StudentInformationResource($studentInformation);
        return response()->json($data);
    }

    public function newSchool(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make(request()->all(), [
                'school_name' => 'required',
                'school_city' => 'required',
                'school_province' => 'required',
                'school_country' => 'required',
            ]);

            if ($validator->fails()) {

                $data['success'] = false;
                $data['response'] = $validator->messages();
                return response()->json($data);
            }
            
            //check for duplicate school name
            $checkSchool = $this->school->where('name', $request->school_name)->first();

            if($checkSchool){
                $data['message'] = 'School Name already existed';
                $data['success'] = false;
                $data['data'] = null;

                return response()->json($data);
            }else{
                $newSchool = new $this->school();
                $newSchool->name = request('school_name');
                $newSchool->city = request('school_city');
                $newSchool->province = request('school_province');
                $newSchool->country = request('school_country');
                $newSchool->save();
            }

            $data['message'] = 'Successfully added new school';
            $data['success'] = true;
            DB::commit();
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['response'] = $e->getMessage();
            $data['message'] = 'Server Error.';
            DB::rollback();
            return response()->json($data);
        }
    }

    public function updateSchool($id)
    {
        $school = $this->school->find($id);

        if (!$school) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $school->name = request('school_name');
        $school->city = request('school_city');
        $school->province = request('school_province');
        $school->country = request('school_country');
        $school->update();

        $data['message'] = 'Successfully updated.';
        $data['success'] = true;
        return response()->json($data);
    }

    public function deleteSchool($id)
    {
        $school = $this->school->find($id);
        if ($school) {
            $data['success'] = true;
            $data['message'] = 'Successfully deleted.';

            //Activity Logs
            storeActivity('School', 'Delete School', "Successfully deleted school : " . $school->id);

            $school->delete();
        } else {
            $data['success'] = false;
            $data['message'] = 'Record not found.';
        }

        return response()->json($data, 200);
    }

    public function updateField($slug)
    {
        $studentInformation = $this->studentInformation
                                    ->where('slug', $slug)
                                    ->first();

        if (!$studentInformation) {
            $data['success'] = false;
            $data['data'] = [];
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        $field = request('field');
        StudentInfoStatusLog::storeLogs($studentInformation->id, $studentInformation->status, request('admissions_officer'), "Updated ".$field." from ".$studentInformation->$field." to ".request('value'));
        $studentInformation->$field = request('value');
        if($field == "type_id"){
            //$program  = $this->studentType->find(request('value'));
            $studentInformation->type_id = request('value');
            $studentInformation->program = request('program');
        }
        if($field == "type_id2"){
            //$program  = $this->studentType->find(request('value'));
            $studentInformation->program2 = request('program');
        }
        if($field == "type_id3"){
            //$program  = $this->studentType->find(request('value'));
            $studentInformation->program3 = request('program');
        }
        if($studentInformation->campus == 'Makati'){
            if($field == 'student_type'){
                if(strpos(request('value'), 'COLLEGE') !== false){
                    $studentInformation->type = 'college';
                }else if(strpos(request('value'), 'DRIVE') !== false){
                    $studentInformation->type = 'drive';
                }else if(strpos(request('value'), 'SHS') !== false){
                    $studentInformation->type = 'shs';
                }else{
                    $studentInformation->type = 'other';
                }
            }

            $studentInformation->sd_company = request('sd_company');
            $studentInformation->sd_position = request('sd_position');
            $studentInformation->sd_degree = request('sd_degree');
        }

        $studentInformation->update();

        $data['message'] = 'Successfully updated.';
        $data['success'] = true;
        $data['data'] = new StudentInformationResource($studentInformation);
        return response()->json($data);
    }

    public function getInformations($status)
    {
        $paginateCount = 10;

        if (request('count_content')) {
            $paginateCount = request('count_content');
        }

        return StudentInformationResource::collection(
            $this->studentInformation->filterByStatus($status)
               ->filterByField(request('search_field'), request('search_data'))
               ->orderBy('created_at', 'desc')
               ->paginate($paginateCount)
        );
    }

    public function saveStudentTypes($infoID, $types)
    {
        foreach ($types as $key => $typeID) {
            $applyingAndProgram = new $this->applyingAndProgram();
            $applyingAndProgram->student_information_id = $infoID;
            $applyingAndProgram->ref_id = $typeID;
            $applyingAndProgram->ref_type = 'student_type';
            $applyingAndProgram->save();
        }
    }

    public function uploadAttachments($id)
    {
        $validator = Validator::make(request()->all(), [
            'files.*' => 'max:30000'
        ]);

        if ($validator->fails()) {
            $data['success'] = false;
            $data['response'] = $validator->messages();
            $data['message'] = 'The files may not be greater than 30000 kilobytes.';
            return response()->json($data);
        }

        $studentInformation = $this->studentInformation->find($id);

        if (!$studentInformation) {
            $data['success'] = false;
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        foreach (request('files') as $key => $file) {
            $filenameWithExt = $file->getClientOriginalName();
            $filename = now()->format('dmYHisu');
            $extension = $file->getClientOriginalExtension();
            $fileNameToStore = $filename . '.' . $extension;
            $path = $file->storeAs('public/acceptance_attachments', $fileNameToStore);

            $attachment = new $this->acceptanceLetterAttachment();
            $attachment->student_information_id = $id;
            $attachment->filename = $filename;
            $attachment->orig_filename =  $filenameWithExt;
            $attachment->filetype = $extension;
            $attachment->save();
        }

        $data['message'] = 'Successfully updated.';
        $data['success'] = true;
        $data['data'] = AcceptanceAttachmentResource::collection(
            $studentInformation->acceptanceAttachments
        );
        return response()->json($data);
    }

    public function sendAcceptanceMail($id)
    {
        DB::beginTransaction();

        try {
            $studentInformation = $this->studentInformation->find($id);

            if (!$studentInformation) {
                $data['success'] = false;
                $data['message'] = 'Not found';
                return response()->json($data);
            }

            $studentInformation->acceptance_letter = request('content');
            $studentInformation->acceptance_letter_sent_date = now();
            $studentInformation->status = 'For Reservation';
            $studentInformation->update();

            $response = Http::get($url.'unity/get_active_sem');              

            $dataResp = $response->body();            
            $dataResp = json_decode($dataResp);
            
            if($studentInformation->campus == 'Makati'){
                //Email acceptance letter
                Mail::to($studentInformation->email)->send(
                    new SendAcceptanceLetterMakatiMail($studentInformation, $dataResp->active_sem)
                );
            }else if($studentInformation->campus == 'Cebu'){
                //Email acceptance letter
                Mail::to($studentInformation->email)->send(
                    new SendAcceptanceLetterMail($studentInformation, $dataResp->active_sem)
                );
            }

            $data['message'] = 'Successfully sent.';
            $data['success'] = true;
            $data['data'] = new StudentInformationResource($studentInformation);
            DB::commit();
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['response'] = $e->getMessage();
            $data['message'] = 'Server Error.';
            DB::rollback();
            return response()->json($data);
        }
    }

    public function deleteAttachment($id)
    {
        $attachment = $this->acceptanceLetterAttachment->find($id);

        if (!$attachment) {
            $data['success'] = false;
            $data['message'] = 'Not found';
            return response()->json($data);
        }

        \Storage::delete('public/acceptance_attachments/' . $attachment->filename . '.' . $attachment->filetype);

        $attachment->delete();

        $data['success'] = true;
        $data['message'] = 'Successfully deleted!';
        return response()->json($data);
    }

    public function saveDesiredPrograms($infoID, $programs)
    {
        foreach ($programs as $key => $programID) {
            $applyingAndProgram = new $this->applyingAndProgram();
            $applyingAndProgram->student_information_id = $infoID;
            $applyingAndProgram->ref_id = $programID;
            $applyingAndProgram->ref_type = 'desired_program';
            $applyingAndProgram->save();
        }
    }

    public function download($status)
    {
        return Excel::download(new StudentInformationExport($status), 'student_informations.xlsx');
    }

    public function uploadVoucher($slug)
    {
        $studentInformation = $this->studentInformation
                                    ->where('slug', $slug)
                                    ->first();

    }

    public function getApplicantsByField($sem, $field, $value)
    {
        $applicants = $this->studentInformation->where('syid', $sem)
                                                ->where($field, $value)
                                                ->where('status','Enrolled')
                                                ->get();
        
        return StudentInformationResource::collection($applicants);
    }

    public function scholarshipVerification(Request $request)
    {
        $rules =  [
            'email' => 'required|email',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $responseData['success'] = false;
            $responseData['response'] = $validator->messages();
            $responseData['message'] = 'Validation Error.';

            return $responseData;
        } else {
            $applicant = '';
            $emailToken = $isCompleteRequirements = 0;
            $email = $request->email;
            //check if applicant
            if($request->token){
                $applicant = $this->studentInformation->where('email', $email)->where('slug', $request->token)->first();
                $emailToken = 1;
            }else{
                $applicant = $this->studentInformation->where('email', $email)->first();
            }

            
            if($applicant){
                $campus = $applicant->campus;
                if($emailToken == 0){
                    Mail::to($email)->send(new VerifyRequirementsMail($applicant));
                }
                
                //check if complete requirements
                $requirements = $this->admissionFile->where('slug', $applicant->slug)->get();
                if($requirements){
                    //check if scholarship exists
                    $checkScholarshipExist = $this->studentInformation->scholarship;
                    if(count($requirements) >= 3){
                        
                        if(count($checkScholarshipExist) > 0){
                            //redirect to application already submitted page
                            if($emailToken == 1){
                                dd('Page for Scholarship Application already submitted');
                                return redirect ('https://cebu.iacademy.edu.ph');
                            }
                        }else{
                            //redirect to submission of requirements
                            if($emailToken == 1){
                                dd('Page for scholarship submission');
                                return redirect ('https://cebu.iacademy.edu.ph');
                            }
                        }
                        $isCompleteRequirements = 1;
                    }
                }
                
                if($isCompleteRequirements == 0){
                    //redirect to submission of requirements
                    if($emailToken == 1){
                        if($campus == 'Makati'){
                            return redirect('https://sms-makati.iacademy.edu.ph/site/initial_requirements/' . $applicant->slug);
                        }else if($campus == 'Cebu'){
                            return redirect('https://cebu.iacademy.edu.ph/site/initial_requirements/' . $applicant->slug);
                        }
                    }
                }
            }else{
                //email redirect to application page
                $responseData['success'] = false;
                $responseData['message'] = 'No application found';
                
                return response()->json($responseData);    
            }

            $responseData['success'] = true;
            $responseData['message'] = 'Application verification has been sent to email';
            
            return response()->json($responseData);
        }
    }
}
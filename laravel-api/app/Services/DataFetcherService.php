<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DataFetcherService
{
    /**
     * Resolve student profile by portal token (strGSuiteEmail) with latest registration.
     * Mirrors logic in PortalController::studentData to maintain parity.
     *
     * Returns associative array compatible with StudentResource or null if not found/incomplete.
     */
    public function getStudentByToken(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $user = DB::table('tb_mas_users')
            ->join('tb_mas_programs', 'tb_mas_users.intProgramID', '=', 'tb_mas_programs.intProgramID')
            ->where('strGSuiteEmail', $token)
            ->select('tb_mas_users.*', 'tb_mas_programs.strProgramCode')
            ->first();

        if (!$user) {
            return null;
        }

        $registered = DB::table('tb_mas_registration')
            ->join('tb_mas_sy', 'tb_mas_registration.intAYID', '=', 'tb_mas_sy.intID')
            ->where('intStudentID', $user->intID)
            ->whereNotNull('dteRegistered')
            ->orderBy('dteRegistered', 'desc')
            ->select(
                'tb_mas_registration.*',
                'tb_mas_sy.enumSem',
                'tb_mas_sy.strYearStart',
                'tb_mas_sy.strYearEnd'
            )
            ->first();

        if (!$registered) {
            // Maintain parity: when no registration with dteRegistered, treat as not found
            return null;
        }

        return [
            'first_name'     => $user->strFirstname,
            'last_name'      => $user->strLastname,
            'personal_email' => $user->strEmail,
            'student_number' => $user->strStudentNumber,
            'contact_number' => $user->strMobileNumber,
            'course_id'      => $user->intProgramID,
            'course_name'    => $user->strProgramCode,
            'last_term'      => $registered->enumSem . ' Term',
            'last_term_sy'   => $registered->strYearStart . '-' . $registered->strYearEnd,
        ];
    }

    /**
     * Resolve student core info by student number with latest registration if available.
     * Returns a minimal profile; last_term fields may be null if no registration found.
     */
    public function getStudentByNumber(string $studentNumber): ?array
    {
        if ($studentNumber === '') {
            return null;
        }

        $user = DB::table('tb_mas_users')
            ->join('tb_mas_programs', 'tb_mas_users.intProgramID', '=', 'tb_mas_programs.intProgramID')
            ->where('tb_mas_users.strStudentNumber', $studentNumber)
            ->select('tb_mas_users.*', 'tb_mas_programs.strProgramCode')
            ->first();

        if (!$user) {
            return null;
        }

        $registered = DB::table('tb_mas_registration')
            ->join('tb_mas_sy', 'tb_mas_registration.intAYID', '=', 'tb_mas_sy.intID')
            ->where('intStudentID', $user->intID)
            ->whereNotNull('dteRegistered')
            ->orderBy('dteRegistered', 'desc')
            ->select(
                'tb_mas_registration.*',
                'tb_mas_sy.enumSem',
                'tb_mas_sy.strYearStart',
                'tb_mas_sy.strYearEnd'
            )
            ->first();

        return [
            'first_name'     => $user->strFirstname,
            'last_name'      => $user->strLastname,
            'personal_email' => $user->strEmail,
            'student_number' => $user->strStudentNumber,
            'contact_number' => $user->strMobileNumber,
            'course_id'      => $user->intProgramID,
            'course_name'    => $user->strProgramCode,
            'last_term'      => $registered ? ($registered->enumSem . ' Term') : null,
            'last_term_sy'   => $registered ? ($registered->strYearStart . '-' . $registered->strYearEnd) : null,
        ];
    }

    /**
     * Compute balances using tb_mas_student_ledger (charges/payments) and tb_mas_transactions as fallback.
     * Positive amounts in tb_mas_student_ledger are charges, negative are payments.
     */
    public function getStudentBalances(string $studentNumber): array
    {
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();

        if (!$user) {
            return [
                'student_number'    => $studentNumber,
                'total_due'         => 0.00,
                'total_paid'        => 0.00,
                'outstanding'       => 0.00,
                'last_payment_date' => null,
                'ledger'            => [],
            ];
        }

        // Fetch ledger rows if table exists; otherwise fallback to transactions only
        $ledgerRows = [];
        try {
            $ledgerRows = DB::table('tb_mas_student_ledger as l')
                ->leftJoin('tb_mas_sy as sy', 'l.syid', '=', 'sy.intID')
                ->leftJoin('tb_mas_faculty as f', 'l.added_by', '=', 'f.intID')
                ->leftJoin('tb_mas_scholarships as sc', 'l.scholarship_id', '=', 'sc.intID')
                ->where('l.student_id', $user->intID)
                ->where('l.is_disabled', 0)
                ->orderBy('l.date', 'asc')
                ->select(
                    'l.id',
                    'l.date',
                    'l.name',
                    'l.amount',
                    'l.or_number',
                    'l.remarks',
                    'l.type',
                    'l.added_by',
                    'l.syid',
                    'sy.enumSem',
                    'sy.strYearStart',
                    'sy.strYearEnd',
                    'f.strFirstname as cashier_first',
                    'f.strLastname as cashier_last',
                    'sc.name as scholarship_name'
                )
                ->get()
                ->map(function ($r) {
                    $term = null;
                    if (isset($r->enumSem, $r->strYearStart, $r->strYearEnd)) {
                        $term = sprintf('%s Term %s-%s', $r->enumSem, $r->strYearStart, $r->strYearEnd);
                    }
                    $type = null;
                    if (isset($r->amount)) {
                        $type = ((float)$r->amount) < 0 ? 'payment' : 'charge';
                    } elseif (isset($r->type)) {
                        $type = $r->type;
                    }

                    $cashier = null;
                    if (!empty($r->cashier_first) || !empty($r->cashier_last)) {
                        $cashier = trim(($r->cashier_first ?? '') . ' ' . ($r->cashier_last ?? ''));
                    }

                    return [
                        'id'         => $r->id ?? null,
                        'date'       => $r->date ?? null,
                        'term'       => $term,
                        'name'       => $r->name ?? ($type === 'payment' ? 'Payment' : 'Charge'),
                        'type'       => $type,
                        'amount'     => isset($r->amount) ? (float)abs($r->amount) : 0.00,
                        'raw_amount' => isset($r->amount) ? (float)$r->amount : 0.00,
                        'or_no'      => $r->or_number ?? null,
                        'remarks'    => $r->remarks ?? null,
                        'cashier'    => $cashier,
                        'scholarship_name' => $r->scholarship_name ?? null,
                        'syid'       => $r->syid ?? null,
                    ];
                })
                ->toArray();
        } catch (\Throwable $e) {
            // table might not exist in some environments; ignore and fallback
            $ledgerRows = [];
        }

        $totalCharges = 0.0;
        $totalPayments = 0.0;
        $lastPaymentDate = null;

        foreach ($ledgerRows as $row) {
            $raw = $row['raw_amount'] ?? 0.0;
            if ($raw < 0) {
                $totalPayments += abs($raw);
                // Prefer ledger date for last payment
                if ($row['date'] && (!$lastPaymentDate || $row['date'] > $lastPaymentDate)) {
                    $lastPaymentDate = $row['date'];
                }
            } else {
                $totalCharges += $raw;
            }
        }

        // Fallback/augment last payment date and totals from transactions
        $txRows = DB::table('tb_mas_transactions as t')
            ->join('tb_mas_registration as r', 'r.intRegistrationID', '=', 't.intRegistrationID')
            ->where('r.intStudentID', $user->intID)
            ->select('t.intAmountPaid', 't.dtePaid')
            ->get();

        $totalFromTransactions = 0.0;
        foreach ($txRows as $t) {
            $totalFromTransactions += (float)$t->intAmountPaid;
            if ($t->dtePaid && (!$lastPaymentDate || $t->dtePaid > $lastPaymentDate)) {
                $lastPaymentDate = $t->dtePaid;
            }
        }

        // If there are no explicit ledger payment rows, use transactions as paid total
        if ($totalPayments == 0.0 && $totalFromTransactions > 0) {
            $totalPayments = $totalFromTransactions;
        }

        $outstanding = round($totalCharges - $totalPayments, 2);

        return [
            'student_number'    => $studentNumber,
            'total_due'         => round($totalCharges, 2),
            'total_paid'        => round($totalPayments, 2),
            'outstanding'       => $outstanding,
            'last_payment_date' => $lastPaymentDate,
            'ledger'            => array_map(function ($r) {
                // remove helper field
                unset($r['raw_amount']);
                return $r;
            }, $ledgerRows),
        ];
    }

    /**
     * Retrieve academic records. When includeGrades=true, include grade fields.
     * If $term provided, it should be a tb_mas_sy.intID; otherwise returns all terms.
     */
    public function getStudentRecords(string $studentNumber, ?string $term, bool $includeGrades): array
    {
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();

        $records = [];
        if ($user) {
            $q = DB::table('tb_mas_classlist_student as cls')
                ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
                ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
                ->leftJoin('tb_mas_sy as sy', 'sy.intID', '=', 'cl.strAcademicYear')
                ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'cl.intFacultyID')
                ->leftJoin('tb_mas_faculty as e', 'e.intID', '=', 'cls.enlisted_user')
                ->where('cls.intStudentID', $user->intID);               

            $rows = $q->select(
                'cl.SectionCode as sectionCode',
                's.strCode as code',
                's.strDescription as description',
                's.strUnits as units',
                's.intID as subject_id',
                'cl.intID as classlist_id',
                'cl.strAcademicYear as syid',
                'sy.enumSem',
                'sy.strYearStart',
                'sy.strYearEnd',
                'f.strFirstname as faculty_firstname',
                'f.strLastname as faculty_lastname',
                'e.strFirstname as reg_firstname',
                'e.strLastname as reg_lastname',
                'cls.strRemarks as remarks',
                'cls.floatPrelimGrade as prelim',
                'cls.floatMidtermGrade as midterm',
                'cls.floatFinalsGrade as finals',
                'cls.floatFinalGrade as final'
            )
            ->orderBy('sy.strYearStart', 'asc')
            ->orderBy('sy.enumSem', 'asc')            
            ->get();

            foreach ($rows as $r) {
                $termLabel = null;
                if (isset($r->enumSem, $r->strYearStart, $r->strYearEnd)) {
                    $termLabel = sprintf('%s Term %s-%s', $r->enumSem, $r->strYearStart, $r->strYearEnd);
                }
                $item = [         
                    'faculty_first'=>$r->faculty_firstname,
                    'faculty_last'=>$r->faculty_lastname,      
                    'enlisted_first'=>$r->reg_firstname,
                    'enlisted_last'=>$r->reg_lastname,           
                    'section_code'=> $r->sectionCode,
                    'classlist_id'=> $r->classlist_id,
                    'code'        => $r->code,
                    'description' => $r->description,
                    'units'       => isset($r->units) ? (int)$r->units : null,
                    'subject_id'  => isset($r->subject_id) ? (int)$r->subject_id : null,
                    'syid'        => $r->syid,
                    'remarks'     => $r->remarks,                    
                ];
                if ($includeGrades) {
                    $item['grades'] = [
                        'prelim'  => $r->prelim ?? null,
                        'midterm' => $r->midterm ?? null,
                        'finals'  => $r->finals ?? null,
                        'final'   => $r->final ?? null,
                    ];
                }
                $records[] = $item;
            }
        }

        return [
            'student_number' => $studentNumber,
            'term'           => $term,
            'include_grades' => $includeGrades,
            'records'        => $records,
        ];
    }

    /**
     * Retrieve academic records for a specific term and return a grouped 'terms' shape.
     */
    public function getStudentRecordsByTerm(string $studentNumber, string $term, bool $includeGrades): array
    {
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();

        $records = [];
        $label = null;

        if ($user) {
            $q = DB::table('tb_mas_classlist_student as cls')
                ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
                ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
                ->leftJoin('tb_mas_sy as sy', 'sy.intID', '=', 'cl.strAcademicYear')
                ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'cl.intFacultyID')
                ->leftJoin('tb_mas_faculty as e', 'e.intID', '=', 'cls.enlisted_user')
                ->where('cls.intStudentID', $user->intID)
                ->where('cl.strAcademicYear', $term);

            $rows = $q->select(
                'cl.SectionCode as sectionCode',
                's.strCode as code',
                's.strDescription as description',
                's.strUnits as units',
                's.intID as subject_id',
                'cl.intID as classlist_id',
                'cl.strAcademicYear as syid',
                'sy.enumSem',
                'sy.strYearStart',
                'sy.strYearEnd',
                'f.strFirstname as faculty_firstname',
                'f.strLastname as faculty_lastname',
                'e.strFirstname as reg_firstname',
                'e.strLastname as reg_lastname',
                'cls.strRemarks as remarks',
                'cls.floatPrelimGrade as prelim',
                'cls.floatMidtermGrade as midterm',
                'cls.floatFinalsGrade as finals',
                'cls.floatFinalGrade as final'
            )
            ->orderBy('s.strCode', 'asc')
            ->get();

            foreach ($rows as $r) {
                if ($label === null && isset($r->enumSem, $r->strYearStart, $r->strYearEnd)) {
                    $label = sprintf('%s Term %s-%s', $r->enumSem, $r->strYearStart, $r->strYearEnd);
                }
                $item = [         
                    'faculty_first'=>$r->faculty_firstname,
                    'faculty_last'=>$r->faculty_lastname,      
                    'enlisted_first'=>$r->reg_firstname,
                    'enlisted_last'=>$r->reg_lastname,           
                    'section_code'=> $r->sectionCode,
                    'classlist_id'=> $r->classlist_id,
                    'code'        => $r->code,
                    'description' => $r->description,
                    'units'       => isset($r->units) ? (int)$r->units : null,
                    'subject_id'  => isset($r->subject_id) ? (int)$r->subject_id : null,
                    'term'        => $label,
                    'syid'        => $r->syid,
                    'remarks'     => $r->remarks,                    
                ];
                if ($includeGrades) {
                    $item['grades'] = [
                        'prelim'  => $r->prelim ?? null,
                        'midterm' => $r->midterm ?? null,
                        'finals'  => $r->finals ?? null,
                        'final'   => $r->final ?? null,
                    ];
                }
                $records[] = $item;
            }

            if ($label === null) {
                $sy = DB::table('tb_mas_sy')->where('intID', $term)->first();
                if ($sy) {
                    $label = sprintf('%s Term %s-%s', $sy->enumSem, $sy->strYearStart, $sy->strYearEnd);
                }
            }
        }

        return [
            'student_id' =>$user->intID,
            'student_number' => $studentNumber,
            'term'           => $term,
            'include_grades' => $includeGrades,
            'terms'          => [[
                'syid'    => $term,
                'label'   => $label,
                'term'    => $label,
                'records' => $records,
            ]],
        ];
    }

    /**
     * Retrieve transaction ledger using tb_mas_transactions joined to registration and users.
     */
    public function getStudentLedger(string $studentNumber): array
    {
        $transactions = DB::table('tb_mas_transactions as t')
            ->join('tb_mas_registration as r', 'r.intRegistrationID', '=', 't.intRegistrationID')
            ->join('tb_mas_users as u', 'u.intID', '=', 'r.intStudentID')
            ->where('u.strStudentNumber', $studentNumber)
            ->orderBy('t.dtePaid', 'asc')
            ->orderBy('t.intORNumber', 'asc')
            ->select(
                't.intTransactionID',
                'u.strStudentNumber',
                't.strTransactionType',
                't.intAmountPaid',
                't.intORNumber',
                't.dtePaid'
            )
            ->get()
            ->map(function ($t) {
                return [
                    'id'             => $t->intTransactionID,
                    'student_number' => $t->strStudentNumber,
                    'type'           => $t->strTransactionType,
                    'method'         => null,
                    'amount'         => (float)$t->intAmountPaid,
                    'or_no'          => $t->intORNumber,
                    'posted_at'      => $t->dtePaid,
                    'remarks'        => null,
                ];
            })
            ->toArray();

        return [
            'student_number' => $studentNumber,
            'transactions'   => $transactions,
        ];
    }
}

<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EnrolledStudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @var int
     */
    protected int $syid;

    /**
     * @param int $syid School year ID (term) to export
     */
    public function __construct(int $syid)
    {
        $this->syid = $syid;
    }

    /**
     * Build the dataset:
     *  - Filter: r.intAYID = :syid AND r.intROG = 1
     *  - Join users and resolve program via COALESCE(r.current_program, u.intProgramID)
     *  - Return one row per student (DISTINCT by student)
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $syid = $this->syid;

        $rows = DB::table('tb_mas_registration as r')
            ->join('tb_mas_users as u', 'u.intID', '=', 'r.intStudentID')
            ->leftJoin('tb_mas_programs as rp', 'rp.intProgramID', '=', 'r.current_program')
            ->leftJoin('tb_mas_programs as up', 'up.intProgramID', '=', 'u.intProgramID')
            ->where('r.intAYID', $syid)
            ->where('r.intROG', 1)
            ->select(
                'u.strStudentNumber as student_number',
                'u.strFirstname as first_name',
                'u.strLastname as last_name',
                'u.strMiddlename as middle_name',
                DB::raw('COALESCE(rp.strProgramCode, up.strProgramCode) as program_code'),
                'u.intID as student_id',
                'r.dteRegistered as date_enrolled',
                'r.enumStudentType as type'
            )
            // ensure one row per student in case of duplicates from joins
            ->distinct()
            ->orderBy('u.strStudentNumber', 'desc')
            ->get();

        return $rows;
    }

    /**
     * Headings for the spreadsheet.
     *
     * @return array{string}
     */
    public function headings(): array
    {
        return [
            'Student Number',
            'First Name',
            'Last Name',
            'Middle Name',
            'Program Code',
            'Date Enrolled',
            'Type',
        ];
    }

    /**
     * Map each row object to an exportable flat array.
     *
     * @param  object  $row
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        $date = '';
        if (!empty($row->date_enrolled)) {
            $ts = strtotime($row->date_enrolled);
            $date = $ts ? date('Y-m-d', $ts) : (string) $row->date_enrolled;
        }

        return [
            $row->student_number ?? '',
            $row->first_name ?? '',
            $row->last_name ?? '',
            $row->middle_name ?? '',
            $row->program_code ?? '',
            $date,
            $row->type ?? '',
        ];
    }
}

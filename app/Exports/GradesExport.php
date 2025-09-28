<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GradesExport implements FromCollection, WithMapping, WithHeadings
{
    /**
     * grade
     *
     * @var mixed
     */
    protected $grades;

    /**
     * __construct
     *
     * @param  mixed $grade
     * @return void
     */
    public function __construct($grades) {
        $this->grades = $grades;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->grades;
    }

    public function map($grades) : array {
        $row = [
            $grades->exam->title,
            $grades->exam_session->title,
            $grades->student->name,
            $grades->exam->classroom->title,
            $grades->exam->lesson->title,
            $grades->grade,
        ];
        // append anti-cheat info if available
        if (isset($grades->cheat_count)) {
            $row[] = $grades->cheat_count;
            $row[] = $grades->cheat_status ?? 'OK';
        }
        return $row;
    }

    public function headings() : array {
        $base = [
            'Ujian',
            'Sesi',
            'Nama Siswa',
            'Kelas',
            'Pelajaran',
            'Nilai',
        ];
        // add anti-cheat columns
        $base[] = 'Jumlah Kecurangan';
        $base[] = 'Status Kecurangan';
        return $base;
    }
}

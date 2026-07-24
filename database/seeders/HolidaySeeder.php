<?php

namespace Database\Seeders;

use Domain\Calendar\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['date' => '2026-01-01', 'summary' => 'Hari Tahun Baru', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-01-16', 'summary' => 'Isra Mikraj Nabi Muhammad', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-02-16', 'summary' => 'Cuti Bersama Tahun Baru Imlek', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-02-17', 'summary' => 'Tahun Baru Imlek', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-02-19', 'summary' => '1 Ramadan', 'description' => 'Perayaan', 'is_national_holiday' => false],
            ['date' => '2026-03-18', 'summary' => 'Cuti Bersama Hari Suci Nyepi', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-03-19', 'summary' => 'Hari Suci Nyepi (Tahun Baru Saka)', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-03-20', 'summary' => 'Cuti Bersama Idul Fitri', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-03-21', 'summary' => 'Hari Idul Fitri', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-03-22', 'summary' => 'Hari Idul Fitri', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-03-23', 'summary' => 'Cuti Bersama Idul Fitri', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-03-24', 'summary' => 'Cuti Bersama Idul Fitri', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-04-03', 'summary' => 'Wafat Isa Almasih', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-04-05', 'summary' => 'Hari Paskah', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-05-01', 'summary' => 'Hari Buruh Internasional', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-05-14', 'summary' => 'Kenaikan Isa Al Masih', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-05-15', 'summary' => 'Cuti Bersama Kenaikan Isa Al Masih', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-05-27', 'summary' => 'Idul Adha (Lebaran Haji)', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-05-28', 'summary' => 'Idul Adha (Lebaran Haji)', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-05-31', 'summary' => 'Hari Raya Waisak', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-06-01', 'summary' => 'Hari Lahir Pancasila', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-06-16', 'summary' => 'Hari Kedua Muharram', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-06-17', 'summary' => 'Satu Muharam / Tahun Baru Hijriah', 'description' => 'Perayaan', 'is_national_holiday' => false],
            ['date' => '2026-08-17', 'summary' => 'Hari Proklamasi Kemerdekaan R.I.', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-08-25', 'summary' => 'Maulid Nabi Muhammad', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-12-24', 'summary' => 'Cuti Bersama Natal', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-12-25', 'summary' => 'Hari Raya Natal', 'description' => 'Hari libur nasional', 'is_national_holiday' => true],
            ['date' => '2026-12-31', 'summary' => 'Malam Tahun Baru', 'description' => 'Perayaan', 'is_national_holiday' => false],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date'], 'summary' => $holiday['summary']],
                $holiday
            );
        }
    }
}

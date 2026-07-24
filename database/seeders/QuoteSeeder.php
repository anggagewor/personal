<?php

namespace Database\Seeders;

use Domain\Quote\Models\Quote;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $quotes = [
            ['content' => 'Kesuksesan adalah hasil dari persiapan, kerja keras, dan belajar dari kegagalan.', 'author' => 'Colin Powell'],
            ['content' => 'Jangan menunggu kesempatan, ciptakanlah.', 'author' => 'George Bernard Shaw'],
            ['content' => 'Satu-satunya cara untuk melakukan pekerjaan hebat adalah mencintai apa yang kamu lakukan.', 'author' => 'Steve Jobs'],
            ['content' => 'Masa depan milik mereka yang percaya pada keindahan mimpi-mimpi mereka.', 'author' => 'Eleanor Roosevelt'],
            ['content' => 'Jangan biarkan hari kemarin mengambil terlalu banyak waktu hari ini.', 'author' => 'Will Rogers'],
            ['content' => 'Perjalanan seribu langkah dimulai dari satu langkah pertama.', 'author' => 'Lao Tzu'],
            ['content' => 'Keberhasilan bukan tentang seberapa tinggi kamu mendaki, tapi seberapa baik kamu memantul saat jatuh.', 'author' => null],
            ['content' => 'Hidup ini seperti bersepeda. Untuk menjaga keseimbangan, kamu harus terus bergerak.', 'author' => 'Albert Einstein'],
            ['content' => 'Pendidikan adalah senjata paling ampuh untuk mengubah dunia.', 'author' => 'Nelson Mandela'],
            ['content' => 'Waktu terbaik untuk menanam pohon adalah dua puluh tahun yang lalu. Waktu terbaik kedua adalah sekarang.', 'author' => 'Pepatah Tiongkok'],
            ['content' => 'Jangan takut gagal, takutlah untuk tidak pernah mencoba.', 'author' => null],
            ['content' => 'Kamu tidak harus hebat untuk memulai, tapi kamu harus memulai untuk menjadi hebat.', 'author' => 'Zig Ziglar'],
            ['content' => 'Setiap hari adalah kesempatan baru untuk menjadi lebih baik dari kemarin.', 'author' => null],
            ['content' => 'Kesulitan mempersiapkan orang biasa untuk takdir yang luar biasa.', 'author' => 'C.S. Lewis'],
            ['content' => 'Bukan seberapa keras kamu dipukul, tapi seberapa keras kamu bisa terpukul dan terus maju.', 'author' => 'Rocky Balboa'],
            ['content' => 'Impian tidak bekerja kecuali kamu yang mengerjakannya.', 'author' => 'John C. Maxwell'],
            ['content' => 'Sedikit kemajuan setiap hari menghasilkan hasil yang besar.', 'author' => null],
            ['content' => 'Orang yang berani gagal besar dapat mencapai hal yang besar pula.', 'author' => 'Robert F. Kennedy'],
            ['content' => 'Jangan pernah menyerah. Hal-hal besar membutuhkan waktu.', 'author' => null],
            ['content' => 'Disiplin adalah jembatan antara tujuan dan pencapaian.', 'author' => 'Jim Rohn'],
        ];

        foreach ($quotes as $quote) {
            Quote::create($quote);
        }
    }
}

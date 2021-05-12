<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jurusans = array(
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1014,
                "nama" => "Teknik Survei dan Pemetaan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1023,
                "nama" => "Teknik Gambar Bangunan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1027,
                "nama" => "Desain Pemodelan dan Informasi Bangunan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1049,
                "nama" => "Teknik Konstruksi Batu dan Beton-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1051,
                "nama" => "Bisnis Konstruksi dan Properti-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1058,
                "nama" => "Teknik Konstruksi Baja-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1076,
                "nama" => "Teknik Konstruksi Kayu-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1079,
                "nama" => "Konstruksi Jalan, Irigasi dan Jembatan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1085,
                "nama" => "Teknik Furnitur-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1094,
                "nama" => "Teknik Plambing dan Sanitasi-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1097,
                "nama" => "Konstruksi Gedung, Sanitasi dan Perawatan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1103,
                "nama" => "Teknik Instalasi Tenaga Listrik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1104,
                "nama" => "Teknik Instalasi Tenaga Listrik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1112,
                "nama" => "Teknik Distribusi Tenaga Listrik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1156,
                "nama" => "Teknik Transmisi Tenaga Listrik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1165,
                "nama" => "Teknik Pembangkit Tenaga Listrik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1174,
                "nama" => "Teknik Audio -Video-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1192,
                "nama" => "Teknik Elektronika  Industri-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1194,
                "nama" => "Teknik Elektronika Daya dan Komunikasi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1196,
                "nama" => "Instrumentasi Medik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1218,
                "nama" => "Teknik Pendinginan dan Tata Udara-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1227,
                "nama" => "Teknik Pengelasan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1236,
                "nama" => "Teknik Fabrikasi Logam-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1239,
                "nama" => "Teknik Fabrikasi Logam dan Manufaktur-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1245,
                "nama" => "Teknik Pengecoran Logam-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1254,
                "nama" => "Teknik Pemesinan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1263,
                "nama" => "Teknik Pemeliharaan Mekanik Industri-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1267,
                "nama" => "Teknik Mekanik Industri-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1272,
                "nama" => "Teknik Gambar Mesin-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1275,
                "nama" => "Teknik Perancangan dan Gambar Mesin-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1289,
                "nama" => "Teknik Kendaraan Ringan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1291,
                "nama" => "Teknik Kendaraan Ringan Otomotif-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1298,
                "nama" => "Teknik Alat Berat-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1307,
                "nama" => "Teknik Perbaikan Bodi Otomotif-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1309,
                "nama" => "Teknik Bodi Otomotif-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1316,
                "nama" => "Teknik Sepeda Motor-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1319,
                "nama" => "Teknik dan Bisnis Sepeda Motor-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1325,
                "nama" => "Pemesinan Pesawat Udara-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1334,
                "nama" => "Konstruksi Rangka Pesawat Udara-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1343,
                "nama" => "Konstruksi Badan Pesawat Udara-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1352,
                "nama" => "Air Frame dan Power Plant-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1369,
                "nama" => "Pemeliharaan dan Perbaikan Instrumen Elektronika Pesawat Udara-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1371,
                "nama" => "Electrical Avionics-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1378,
                "nama" => "Kelistrikan Pesawat Udara-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1387,
                "nama" => "Elektronika Pesawat Udara-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1396,
                "nama" => "Teknik Konstruksi Kapal Baja-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1399,
                "nama" => "Konstruksi Kapal Baja-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1405,
                "nama" => "Teknik Pengelasan Kapal-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1409,
                "nama" => "Konstruksi Kapal Non Baja-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1414,
                "nama" => "Teknik Instalasi Pemesinan Kapal-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1417,
                "nama" => "Teknik Pemesinan Kapal-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1423,
                "nama" => "Kelistrikan Kapal-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1426,
                "nama" => "Teknik Kelistrikan Kapal-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1432,
                "nama" => "Teknik Gambar Rancang Bangun Kapal-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1435,
                "nama" => "Desain dan Rancang Bangun Kapal-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1449,
                "nama" => "Teknik Konstruksi Kapal Kayu-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1458,
                "nama" => "Interior Kapal-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1485,
                "nama" => "Teknik Pemintalan Serat Buatan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1494,
                "nama" => "Teknik Pembuatan Benang-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1503,
                "nama" => "Teknik Pembuatan Kain-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1512,
                "nama" => "Teknik Penyempurnaan Tekstil-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1538,
                "nama" => "Persiapan Grafika-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1541,
                "nama" => "Desain  Grafika-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1547,
                "nama" => "Produksi Grafika-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1556,
                "nama" => "Geologi Pertambangan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1574,
                "nama" => "Kontrol Proses-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1583,
                "nama" => "Kontrol Mekanik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1585,
                "nama" => "Instrumentasi dan Otomatisasi Proses-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1592,
                "nama" => "Teknik Instrumentasi Logam-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1609,
                "nama" => "Kimia Industri-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1625,
                "nama" => "Analisis Pengujian Laboratorium-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1627,
                "nama" => "Kimia Analisis-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1639,
                "nama" => "Kimia Tekstil-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1645,
                "nama" => "Nautika Kapal Niaga-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1654,
                "nama" => "Teknika Kapal Niaga-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1663,
                "nama" => "Nautika Kapal Penangkap Ikan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1672,
                "nama" => "Teknika Kapal Penangkap Ikan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1707,
                "nama" => "Teknik Mekatronika-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1725,
                "nama" => "Teknik Pemboran Minyak-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1734,
                "nama" => "Teknik Pengolahan Minyak, Gas dan Petro Kimia-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1743,
                "nama" => "Teknik Otomasi Industri-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1746,
                "nama" => "Teknik Tenaga Listrik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1752,
                "nama" => "Teknik Ototronik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1754,
                "nama" => "Teknik dan Manajemen Perawatan Otomotif-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1757,
                "nama" => "Otomotif Daya dan Konversi Energi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1769,
                "nama" => "Teknik dan Manajemen Transportasi-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1778,
                "nama" => "Teknik Konstruksi Kapal Fiberglass-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1787,
                "nama" => "Teknik dan Manajemen Produksi-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1796,
                "nama" => "Teknik dan Manajemen Pergudangan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1807,
                "nama" => "Teknik Geomatika-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1809,
                "nama" => "Informasi Geospasial-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1834,
                "nama" => "Airframe Power Plant-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1851,
                "nama" => "Teknik Pengendalian Produksi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1861,
                "nama" => "Teknik Logistik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1869,
                "nama" => "Teknik Pemboran Minyak dan Gas-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1878,
                "nama" => "Teknik Pengolahan Minyak, Gas dan Petrokimia-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1887,
                "nama" => "Teknik Produksi Minyak dan Gas-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1915,
                "nama" => "Teknik Energi Surya, Hidro, dan Angin-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 1931,
                "nama" => "Teknik Energi Biomassa-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2027,
                "nama" => "Teknik Jaringan Akses-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2029,
                "nama" => "Teknik Jaringan Akses Telekomunikasi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2036,
                "nama" => "Teknik Suitsing-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2045,
                "nama" => "Teknik Transmisi Telekomunikasi-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2063,
                "nama" => "Teknik Komputer dan Jaringan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2072,
                "nama" => "Rekayasa Perangkat Lunak-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2089,
                "nama" => "Multi Media-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2091,
                "nama" => "Sistem Informatika, Jaringan dan Aplikasi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2098,
                "nama" => "Teknik Produksi dan Penyiaran Program Radio-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2107,
                "nama" => "Teknik Produksi dan Penyiaran Program Pertelevisian-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2116,
                "nama" => "Animasi-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2127,
                "nama" => "Produksi dan Siaran Program Radio-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2131,
                "nama" => "Produksi dan Siaran Program Televisi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2136,
                "nama" => "Rekayasa Perangkat Lunak-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2139,
                "nama" => "Produksi Film dan Program Televisi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2141,
                "nama" => "Produksi Film-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2145,
                "nama" => "Teknik Komputer dan Jaringan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2154,
                "nama" => "Multimedia-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 2171,
                "nama" => "Teknik Transmisi Telekomunikasi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3014,
                "nama" => "Keperawatan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3016,
                "nama" => "Asisten Keperawatan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3023,
                "nama" => "Keperawatan Gigi-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3025,
                "nama" => "Dental Asisten-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3032,
                "nama" => "Analisis Kesehatan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3035,
                "nama" => "Teknologi Laboratorium Medik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3049,
                "nama" => "Farmasi-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3051,
                "nama" => "Farmasi Klinis dan Komunitas-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3058,
                "nama" => "Perawatan Sosial-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3061,
                "nama" => "Social Care (Keperawatan Sosial)-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3065,
                "nama" => "Caregiver-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3067,
                "nama" => "Farmasi Industri-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 3125,
                "nama" => "Farmasi Industri-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4036,
                "nama" => "Seni Patung-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4054,
                "nama" => "Seni Lukis-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4063,
                "nama" => "Desain dan Produksi Kria Tekstil-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4065,
                "nama" => "Kriya Kreatif Batik dan Tekstil-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4072,
                "nama" => "Desain dan Produksi Kria Kulit-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4075,
                "nama" => "Kriya Kreatif Kulit dan Imitasi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4089,
                "nama" => "Desain dan Produksi Kria Keramik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4091,
                "nama" => "Kriya Kreatif Keramik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4098,
                "nama" => "Desain dan Produksi Kria Logam-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4102,
                "nama" => "Kriya Kreatif Logam dan Perhiasan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4107,
                "nama" => "Desain dan Produksi Kria Kayu-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4109,
                "nama" => "Kriya Kreatif Kayu dan Rotan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4125,
                "nama" => "Seni  Musik  Non Klasik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4128,
                "nama" => "Seni Musik Populer-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4132,
                "nama" => "Seni Tari-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4134,
                "nama" => "Seni Tari Jawatimuran-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4143,
                "nama" => "Seni Tari Makasar-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4152,
                "nama" => "Seni Tari Minang-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4159,
                "nama" => "Penataan Tari-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4163,
                "nama" => "Seni Pedalangan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4169,
                "nama" => "Seni Pedalangan Yogyakarta-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4178,
                "nama" => "Seni Pedalangan Surakarta-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4187,
                "nama" => "Seni Pedalangan Jawatimuran-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4196,
                "nama" => "Seni Pedalangan Bali-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4205,
                "nama" => "Seni Tari Sunda-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4214,
                "nama" => "Seni Tari Bali-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4223,
                "nama" => "Seni Tari Surakarta-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4232,
                "nama" => "Seni Tari Yogyakarta-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4249,
                "nama" => "Seni Tari Banyumasan-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4258,
                "nama" => "Seni Karawitan Jawatimuran-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4267,
                "nama" => "Seni Karawitan Makassar-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4276,
                "nama" => "Seni Karawitan Minang-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4285,
                "nama" => "Seni Karawitan Sunda-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4294,
                "nama" => "Seni Karawitan Surakarta-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4303,
                "nama" => "Seni Karawitan Yogyakarta-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4312,
                "nama" => "Seni Karawitan Bali-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4329,
                "nama" => "Seni Karawitan Banyumasan-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4331,
                "nama" => "Penataan Karawitan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4338,
                "nama" => "Seni Teater-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4347,
                "nama" => "Desain Komunikasi Visual-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4365,
                "nama" => "Seni Musik Klasik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4409,
                "nama" => "Usaha Perjalanan Wisata-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4418,
                "nama" => "Akomodasi Perhotelan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4421,
                "nama" => "Perhotelan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4424,
                "nama" => "Wisata Bahari dan Ekowisata-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4426,
                "nama" => "Hotel dan Restoran-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4427,
                "nama" => "Jasa Boga-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4436,
                "nama" => "Patiseri-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4438,
                "nama" => "Tata Boga-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4445,
                "nama" => "Kecantikan Kulit-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4453,
                "nama" => "Seni Karawitan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4454,
                "nama" => "Kecantikan Rambut-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4457,
                "nama" => "Tata Kecantikan Kulit dan Rambut-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4459,
                "nama" => "Spa dan Beauty Therapy-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4463,
                "nama" => "Busana Butik-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4472,
                "nama" => "Desain Produk Interior dan Landscaping-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4489,
                "nama" => "Seni Tari Betawi-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4498,
                "nama" => "Seni Karawitan Betawi-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4509,
                "nama" => "Pemeranan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4518,
                "nama" => "Tata Artistik Teater-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4526,
                "nama" => "Tata Busana-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4527,
                "nama" => "Desain Fesyen-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4537,
                "nama" => "Desain Interior dan Teknik Furnitur-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4545,
                "nama" => "Usaha Perjalanan Wisata-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4609,
                "nama" => "Seni Lukis-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4627,
                "nama" => "Desain Komunikasi Visual-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4635,
                "nama" => "Animasi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4697,
                "nama" => "Seni Musik Klasik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4715,
                "nama" => "Seni Patung-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4729,
                "nama" => "Seni Pedalangan Banyumasan-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 4738,
                "nama" => "Seni Tari Bengkulu-seni etnis",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5032,
                "nama" => "Teknologi Pengolahan Hasil Pertanian-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5035,
                "nama" => "Agribisnis Pengolahan Hasil Pertanian-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5058,
                "nama" => "Mekanisasi Pertanian-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5067,
                "nama" => "Agribisnis Rumput Laut-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5069,
                "nama" => "Agribisnis Rumput Laut-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5094,
                "nama" => "Agribisnis Ternak Ruminansia-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5103,
                "nama" => "Agribisnis Ternak Unggas-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5106,
                "nama" => "Industri Peternakan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5129,
                "nama" => "Kehutanan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5156,
                "nama" => "Pengawasan Mutu-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5183,
                "nama" => "Agribisnis Tanaman Perkebunan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5245,
                "nama" => "Penyuluhan Pertanian-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5254,
                "nama" => "Perawatan Kesehatan Ternak-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5263,
                "nama" => "Agribisnis Tanaman Pangan dan Holtikultura-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5272,
                "nama" => "Agribisnis Pembibitan dan Kultur Jaringan Tanaman-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5274,
                "nama" => "Pemuliaan dan Perbenihan Tanaman-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5276,
                "nama" => "Lanskap dan Pertamanan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5278,
                "nama" => "Produksi dan Pengelolaan Perkebunan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5281,
                "nama" => "Agribisnis Organik Ekologi-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5289,
                "nama" => "Agribisnis Perikanan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5298,
                "nama" => "Agribisnis Aneka Ternak-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5310,
                "nama" => "Alat Mesin Pertanian-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5319,
                "nama" => "Otomatisasi Pertanian-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5329,
                "nama" => "Teknik Inventarisasi dan Pemetaan Hutan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5337,
                "nama" => "Teknik Konservasi Sumber Daya Hutan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5346,
                "nama" => "Teknik Rehabilitasi dan Reklamasi Hutan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5357,
                "nama" => "Teknologi Produksi Hasil Hutan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5381,
                "nama" => "Agribisnis Perikanan Air Tawar-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5383,
                "nama" => "Agribisnis Perikanan Air Payau dan Laut-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5385,
                "nama" => "Agribisnis Ikan Hias-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5426,
                "nama" => "Agribisnis Tanaman Pangan dan Hortikultura-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5435,
                "nama" => "Agribisnis Tanaman Perkebunan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5461,
                "nama" => "Agribisnis Ternak Ruminansia-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5471,
                "nama" => "Agribisnis Ternak Unggas-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5486,
                "nama" => "Keperawatan Hewan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5487,
                "nama" => "Kesehatan dan Reproduksi Hewan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5504,
                "nama" => "Pengawasan Mutu Hasil Pertanian-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5505,
                "nama" => "Agroindustri-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5516,
                "nama" => "Nautika Kapal Penangkap Ikan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5532,
                "nama" => "Teknika Kapal Penangkap Ikan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5542,
                "nama" => "Industri Perikanan Laut-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5549,
                "nama" => "Nautika Kapal Niaga-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5559,
                "nama" => "Teknika Kapal Niaga-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 5567,
                "nama" => "Agribisnis Pengolahan Hasil Perikanan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6018,
                "nama" => "Akuntansi-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6021,
                "nama" => "Akuntansi dan Keuangan Lembaga-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6027,
                "nama" => "Perbankan-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6029,
                "nama" => "Perbankan dan Keuangan Mikro-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6037,
                "nama" => "Perbankan Syariah-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6039,
                "nama" => "Manajemen Logistik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6045,
                "nama" => "Administrasi Perkantoran-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6047,
                "nama" => "Otomatisasi dan Tata Kelola Perkantoran-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6054,
                "nama" => "Pemasaran-K06",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6056,
                "nama" => "Bisnis Daring dan Pemasaran-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 6057,
                "nama" => "Retail-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7029,
                "nama" => "Teknik Pembangkit Tenaga Listrik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7039,
                "nama" => "Teknik Jaringan Tenaga Listrik-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7047,
                "nama" => "Teknik Otomasi Industri-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7056,
                "nama" => "Teknik Pendinginan dan Tata Udara-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7065,
                "nama" => "Teknik Pemesinan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7074,
                "nama" => "Teknik Pengelasan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7101,
                "nama" => "Teknik Pengecoran Logam-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7127,
                "nama" => "Pemesinan Pesawat Udara-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7136,
                "nama" => "Konstruksi Badan Pesawat Udara-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7145,
                "nama" => "Kostruksi Rangka Pesawat Udara-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7154,
                "nama" => "Kelistrikan Pesawat Udara-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7171,
                "nama" => "Elektronika Pesawat Udara-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7199,
                "nama" => "Produksi Grafika-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7207,
                "nama" => "Teknik Instrumentasi Logam-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7235,
                "nama" => "Teknik Pemintalan Serat Buatan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7251,
                "nama" => "Teknik Pembuatan Benang-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7261,
                "nama" => "Teknik Pembuatan Kain-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7271,
                "nama" => "Teknik Penyempurnaan Tekstil-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7279,
                "nama" => "Geologi Pertambangan-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7287,
                "nama" => "Kimia Analisis-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7296,
                "nama" => "Kimia Industri-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7331,
                "nama" => "Teknik Alat Berat-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7385,
                "nama" => "Teknik Pengelasan Kapal-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7421,
                "nama" => "Interior Kapal-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7429,
                "nama" => "Teknik Audio Video-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7439,
                "nama" => "Teknik Elektronika Industri-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7447,
                "nama" => "Teknik Mekatronika-K13-rev",
            ),
            array(
                "id" => Str::uuid()->toString(),
                "kode" => 7457,
                "nama" => "Teknik Ototronik-K13-rev",
            ),
        );

        DB::table('jurusans')->insert($jurusans);
    }
}

<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data');

// Headers (Case A)
$dbKeys = [
    'nis', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 
    'alamat', 'no_hp', 'status', 'tanggal_masuk', 'tanggal_keluar',
    'wali_nama', 'wali_nik', 'wali_no_hp', 'wali_alamat', 'wali_pekerjaan',
    'kelas', 'komplek', 'kamar', 'kapasitas_kamar',
    'lemari', 'lemari_tipe', 'jumlah_slot',
    'slot', 'slot_status', 'slot_keterangan'
];

$visualLabels = [
    'NIS', 'Nama Santri', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir',
    'Alamat Santri', 'No HP Santri', 'Status Santri', 'Tanggal Masuk', 'Tanggal Keluar',
    'Nama Wali', 'NIK Wali', 'No HP Wali', 'Alamat Wali', 'Pekerjaan Wali',
    'Kelas', 'Komplek', 'Kamar', 'Kapasitas Kamar',
    'Nama Lemari', 'Tipe Lemari', 'Jumlah Slot Lemari',
    'Nomor Slot', 'Status Slot', 'Keterangan Slot'
];

foreach ($dbKeys as $colIndex => $key) {
    $sheet->setCellValueByColumnAndRow($colIndex + 1, 1, $key);
}
foreach ($visualLabels as $colIndex => $label) {
    $sheet->setCellValueByColumnAndRow($colIndex + 1, 2, $label);
}

// Generate 110 students (55 Putra, 55 Putri)
$classesPa = ['Awaliyah 1 Pa', 'Awaliyah 2 Pa', 'Awaliyah 3 Pa', 'Wustho 1 Pa', 'Wustho 2 Pa', 'Ulya 1 Pa', 'Ulya 2 Pa'];
$classesPi = ['Awaliyah 1 Pi', 'Awaliyah 2 Pi', 'Awaliyah 3 Pi', 'Wustho 1 Pi', 'Wustho 2 Pi', 'Ulya 1 Pi', 'Ulya 2 Pi'];

$complexesPa = ['Komplek A Pa', 'Komplek B Pa', 'Komplek C Pa', 'Komplek D Pa'];
$complexesPi = ['Komplek A Pi', 'Komplek B Pi', 'Komplek C Pi', 'Komplek D Pi'];

$putraCount = 55;
$putriCount = 55;

// Shared Parents Setup
$waliPutra1 = [
    'wali_nama' => 'Budi Santoso',
    'wali_nik' => '3578010203040001',
    'wali_no_hp' => '089876543210',
    'wali_alamat' => 'Jl. Keputih No. 12',
    'wali_pekerjaan' => 'Wiraswasta'
];
$waliPutri2 = [
    'wali_nama' => 'Achmad Yusuf',
    'wali_nik' => '3578010203040002',
    'wali_no_hp' => '085298765432',
    'wali_alamat' => 'Jl. Sukarno Hatta No. 45',
    'wali_pekerjaan' => 'PNS'
];
$waliMix3 = [
    'wali_nama' => 'Heri Utama',
    'wali_nik' => '3578010203040003',
    'wali_no_hp' => '081298765434',
    'wali_alamat' => 'Jl. Gajah Mada No. 100',
    'wali_pekerjaan' => 'Dosen'
];

$rowNumber = 3;

$writeStudentRow = function($sheet, $row, $data) {
    foreach ($data as $colIndex => $val) {
        if ($colIndex === 11) { // NIK Wali (zero-based index 11)
            $sheet->setCellValueExplicitByColumnAndRow($colIndex + 1, $row, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        } else {
            $sheet->setCellValueByColumnAndRow($colIndex + 1, $row, $val);
        }
    }
};

// Generate 55 Putra
for ($i = 1; $i <= $putraCount; $i++) {
    $nis = '1026' . sprintf('%04d', $i);
    $nama = 'Santri Putra ' . $i;
    
    // Shared parent assignment:
    // - Putra index 1 & 2 share Budi Santoso
    // - Putra index 3 & 4 share Heri Utama
    if ($i === 1 || $i === 2) {
        $wali = $waliPutra1;
    } else if ($i === 3 || $i === 4) {
        $wali = $waliMix3;
    } else {
        $wali = [
            'wali_nama' => 'Wali Putra ' . $i,
            'wali_nik' => '357801020304' . sprintf('%04d', $i + 100),
            'wali_no_hp' => '0812' . sprintf('%08d', $i),
            'wali_alamat' => 'Alamat Wali Putra ' . $i,
            'wali_pekerjaan' => 'Pekerjaan ' . $i
        ];
    }
    
    $classIdx = ($i - 1) % count($classesPa);
    $kelas = $classesPa[$classIdx];
    
    // Rooms & Complexes (8 students per room, 4 rooms per complex, 10 capacity)
    $roomNum = (int)ceil($i / 8); 
    $complexIdx = (int)floor(($roomNum - 1) / 4);
    $complexName = $complexesPa[$complexIdx % count($complexesPa)];
    $roomIdxInComplex = (($roomNum - 1) % 4) + 1;
    $roomName = 'Kamar ' . $roomIdxInComplex . ' Pa';
    
    $slotNum = (($i - 1) % 8) + 1;
    
    $lemariTipe = ($slotNum % 2 === 1) ? 'rak buku' : 'lemari baju';
    $lemariName = 'Lemari ' . ($lemariTipe === 'rak buku' ? 'Buku' : 'Baju') . ' ' . $roomIdxInComplex . ' Pa';
    
    $studentData = [
        $nis, $nama, 'L', 'Surabaya', '2008-' . sprintf('%02d', ($i % 12) + 1) . '-15',
        'Jl. Melati No. ' . $i, '0812' . sprintf('%08d', $i + 500000), 'active', '2025-07-15', '',
        $wali['wali_nama'], $wali['wali_nik'], $wali['wali_no_hp'], $wali['wali_alamat'], $wali['wali_pekerjaan'],
        $kelas, $complexName, $roomName, '10',
        $lemariName, $lemariTipe, '10',
        (string)$slotNum, 'active', 'Slot ' . $slotNum
    ];
    
    $writeStudentRow($sheet, $rowNumber, $studentData);
    $rowNumber++;
}

// Generate 55 Putri
for ($i = 1; $i <= $putriCount; $i++) {
    $nis = '2026' . sprintf('%04d', $i);
    $nama = 'Santri Putri ' . $i;
    
    // Shared parent assignment:
    // - Putri index 1 & 2 share Achmad Yusuf
    // - Putri index 3 shares Heri Utama (resulting in Heri Utama having 2 sons & 1 daughter)
    if ($i === 1 || $i === 2) {
        $wali = $waliPutri2;
    } else if ($i === 3) {
        $wali = $waliMix3;
    } else {
        $wali = [
            'wali_nama' => 'Wali Putri ' . $i,
            'wali_nik' => '357802020304' . sprintf('%04d', $i + 100),
            'wali_no_hp' => '0856' . sprintf('%08d', $i),
            'wali_alamat' => 'Alamat Wali Putri ' . $i,
            'wali_pekerjaan' => 'Pekerjaan ' . $i
        ];
    }
    
    $classIdx = ($i - 1) % count($classesPi);
    $kelas = $classesPi[$classIdx];
    
    $roomNum = (int)ceil($i / 8); 
    $complexIdx = (int)floor(($roomNum - 1) / 4);
    $complexName = $complexesPi[$complexIdx % count($complexesPi)];
    $roomIdxInComplex = (($roomNum - 1) % 4) + 1;
    $roomName = 'Kamar ' . $roomIdxInComplex . ' Pi';
    
    $slotNum = (($i - 1) % 8) + 1;
    
    $lemariTipe = ($slotNum % 2 === 1) ? 'rak buku' : 'lemari baju';
    $lemariName = 'Lemari ' . ($lemariTipe === 'rak buku' ? 'Buku' : 'Baju') . ' ' . $roomIdxInComplex . ' Pi';
    
    $studentData = [
        $nis, $nama, 'P', 'Malang', '2009-' . sprintf('%02d', ($i % 12) + 1) . '-20',
        'Jl. Mawar No. ' . $i, '0856' . sprintf('%08d', $i + 500000), 'active', '2025-07-15', '',
        $wali['wali_nama'], $wali['wali_nik'], $wali['wali_no_hp'], $wali['wali_alamat'], $wali['wali_pekerjaan'],
        $kelas, $complexName, $roomName, '10',
        $lemariName, $lemariTipe, '10',
        (string)$slotNum, 'active', 'Slot ' . $slotNum
    ];
    
    $writeStudentRow($sheet, $rowNumber, $studentData);
    $rowNumber++;
}

// Auto size columns
foreach ($sheet->getColumnIterator() as $column) {
    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$writer->save('contoh_import_santri_110_lengkap.xlsx');
echo "110 students Excel file generated successfully!\n";

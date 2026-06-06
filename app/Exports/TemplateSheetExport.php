<?php

namespace App\Exports;

use App\Models\Santri;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class TemplateSheetExport implements FromArray, WithHeadings, WithEvents, WithColumnFormatting, WithTitle
{
    protected $template;
    protected $withData;
    protected $orderedFields;

    public function __construct($template, $withData = false)
    {
        $this->template = $template;
        $this->withData = $withData;
        $this->orderedFields = $this->template->fields->sortBy('pivot.order');
    }

    public function title(): string
    {
        return 'Data';
    }

    public function headings(): array
    {
        $row1Keys   = [];
        $row2Labels = [];

        foreach ($this->orderedFields as $field) {
            $row1Keys[]   = $field->field_key;
            $row2Labels[] = $field->label . ($field->is_required ? ' *' : '');
        }

        return [
            $row1Keys,   // Baris 1 (Hidden Key)
            $row2Labels  // Baris 2 (Visual Label)
        ];
    }

    public function columnFormats(): array
    {
        $formats = [];
        $kolomIndex = 1;

        foreach ($this->orderedFields as $field) {
            $hurufKolom = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($kolomIndex);
            
            // Kolom kritis pembawa angka 0 di depan dipaksa berformat Text ('@')
            if (in_array($field->field_key, ['nis', 'no_hp', 'wali_no_hp', 'wali_nik'])) {
                $formats[$hurufKolom] = '@'; 
            }
            $kolomIndex++;
        }

        return $formats;
    }

    public function array(): array
    {
        $fields = $this->orderedFields->pluck('field_key')->toArray();
        $rows = [];

        // SCENARIO A: JIKA ADMIN DOWNLOAD KOSONGAN (Buat Baris Contoh Abu-abu di Baris 3)
        if (!$this->withData) {
            $rowContoh = [];
            foreach ($fields as $field) {
                switch ($field) {
                    case 'nis': $rowContoh[] = '20260001'; break;
                    case 'nama_lengkap': $rowContoh[] = 'Muhammad Al-Fatih'; break;
                    case 'jenis_kelamin': $rowContoh[] = 'L'; break;
                    case 'no_hp': $rowContoh[] = "'081234567890"; break; 
                    case 'wali_no_hp': $rowContoh[] = "'089876543210"; break;
                    case 'status': $rowContoh[] = 'active'; break;
                    case 'alamat': $rowContoh[] = 'Jl. Pesantren No. 45, Komplek Krapyak'; break;
                    case (strpos($field, 'tanggal') !== false): $rowContoh[] = '2011-03-25'; break;
                    default: $rowContoh[] = 'Contoh Data'; break;
                }
            }
            $rows[] = $rowContoh;
            return $rows;
        }

        // SCENARIO B: JIKA ADMIN DOWNLOAD BERSAMA DATA RIIL
        $santris = Santri::with(['wali', 'kamar.kompleks', 'kelas'])
            ->where('pondok_id', auth()->user()->pondok_id)
            ->get();

        foreach ($santris as $santri) {
            $row = [];

            foreach ($fields as $field) {
                switch ($field) {
                    // Core Attributes Santri
                    case 'nis': $row[] = $santri->nis; break;
                    case 'nama_lengkap': $row[] = $santri->nama_lengkap; break;
                    case 'jenis_kelamin': $row[] = $santri->jenis_kelamin; break;
                    case 'tempat_lahir': $row[] = $santri->tempat_lahir; break;
                    case 'tanggal_lahir': $row[] = $santri->tanggal_lahir ? $santri->tanggal_lahir->format('Y-m-d') : ''; break;
                    case 'alamat': $row[] = $santri->alamat; break;
                    case 'no_hp': $row[] = $santri->no_hp ? "'".$santri->no_hp : ''; break;
                    case 'status': $row[] = $santri->status; break;
                    case 'tanggal_masuk': $row[] = $santri->tanggal_masuk ? $santri->tanggal_masuk->format('Y-m-d') : ''; break;
                    case 'tanggal_keluar': $row[] = $santri->tanggal_keluar ? $santri->tanggal_keluar->format('Y-m-d') : ''; break;

                    // Relasi Kamar, Kelas, Kompleks
                    case 'kelas': $row[] = $santri->kelas->nama ?? ''; break;
                    case 'komplek': $row[] = $santri->kamar?->kompleks?->nama ?? ''; break;
                    case 'kamar': $row[] = $santri->kamar->nama ?? ''; break;

                    // Relasi Wali
                    case 'wali_nama': $row[] = $santri->wali->nama ?? ''; break;
                    case 'wali_no_hp': $row[] = ($santri->wali && $santri->wali->no_hp) ? "'".$santri->wali->no_hp : ''; break;
                    case 'wali_nik': $row[] = ($santri->wali && $santri->wali->nik) ? "'".$santri->wali->nik : ''; break;
                    case 'wali_pekerjaan': $row[] = $santri->wali->pekerjaan ?? ''; break;

                    default:
                        $row[] = $santri->custom_fields[$field] ?? '';
                        break;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalKolom = count($this->orderedFields);
                
                // 1. Sembunyikan Baris 1 (Key Database)
                $sheet->getRowDimension(1)->setVisible(false);
                
                // 2. Styling Header Label (Baris 2)
                $hurufKolomAkhir = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalKolom);
                $sheet->getStyle("A2:{$hurufKolomAkhir}2")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getRowDimension(2)->setRowHeight(28);

                // Jika berkas kosongan, baris 3 (baris contoh) dibuat miring & warna abu-abu
                if (!$this->withData) {
                    $sheet->getStyle("A3:{$hurufKolomAkhir}3")->applyFromArray([
                        'font' => ['italic' => true, 'color' => ['rgb' => '7F8C8D']]
                    ]);
                }

                // 3. Loop Otomatis Pasang Dropdown & Input Message Tanggal
                $kolomIndex = 1;
                foreach ($this->orderedFields as $field) {
                    $hurufKolom = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($kolomIndex);
                    
                    // Auto-fit lebar kolom
                    $sheet->getColumnDimension($hurufKolom)->setAutoSize(true);

                    switch (true) {
                        // ENUM JENIS KELAMIN (L / P)
                        case ($field->field_key === 'jenis_kelamin'):
                            $validation = $sheet->getCell("{$hurufKolom}3")->getDataValidation();
                            $validation->setType(DataValidation::TYPE_LIST);
                            $validation->setErrorStyle(DataValidation::STYLE_STOP);
                            $validation->setAllowBlank(false);
                            $validation->setShowDropDown(true);
                            $validation->setErrorTitle('Pilihan Salah!');
                            $validation->setError('Jenis Kelamin wajib diisi huruf L atau P saja!');
                            $validation->setFormula1('"L,P"');
                            $validation->setSqref("{$hurufKolom}3:{$hurufKolom}2000"); // Pasang masif s/d baris 2000
                            break;
                            
                        // ENUM STATUS
                        case ($field->field_key === 'status'):
                            $validation = $sheet->getCell("{$hurufKolom}3")->getDataValidation();
                            $validation->setType(DataValidation::TYPE_LIST);
                            $validation->setErrorStyle(DataValidation::STYLE_STOP);
                            $validation->setAllowBlank(false);
                            $validation->setShowDropDown(true);
                            $validation->setErrorTitle('Status Gak Valid!');
                            $validation->setError('Harus memilih status: active, nonaktif, lulus, keluar, atau izin');
                            $validation->setFormula1('"active,nonaktif,lulus,keluar,izin"');
                            $validation->setSqref("{$hurufKolom}3:{$hurufKolom}2000");
                            break;

                        // DYNAMIC DROPDOWNS WITH SOFT ALERTS
                        case ($field->field_key === 'kelas'):
                            $validation = $sheet->getCell("{$hurufKolom}3")->getDataValidation();
                            $validation->setType(DataValidation::TYPE_LIST);
                            $validation->setShowDropDown(true);
                            $validation->setShowErrorMessage(false); // Matikan error alert keras
                            $validation->setShowInputMessage(true);
                            $validation->setPromptTitle('Pilihan Kelas');
                            $validation->setPrompt('Pilih Kelas dari daftar, atau ketik Kelas baru.');
                            $validation->setFormula1('Lookups!$A$2:$A$1000');
                            $validation->setSqref("{$hurufKolom}3:{$hurufKolom}2000");
                            break;

                        case ($field->field_key === 'komplek'):
                            $validation = $sheet->getCell("{$hurufKolom}3")->getDataValidation();
                            $validation->setType(DataValidation::TYPE_LIST);
                            $validation->setShowDropDown(true);
                            $validation->setShowErrorMessage(false); // Matikan error alert keras
                            $validation->setShowInputMessage(true);
                            $validation->setPromptTitle('Pilihan Komplek');
                            $validation->setPrompt('Pilih Komplek dari daftar, atau ketik Komplek baru.');
                            $validation->setFormula1('Lookups!$B$2:$B$1000');
                            $validation->setSqref("{$hurufKolom}3:{$hurufKolom}2000");
                            break;

                        case ($field->field_key === 'kamar'):
                            $validation = $sheet->getCell("{$hurufKolom}3")->getDataValidation();
                            $validation->setType(DataValidation::TYPE_LIST);
                            $validation->setShowDropDown(true);
                            $validation->setShowErrorMessage(false); // Matikan error alert keras
                            $validation->setShowInputMessage(true);
                            $validation->setPromptTitle('Pilihan Kamar');
                            $validation->setPrompt('Pilih Kamar dari daftar, atau ketik Kamar baru.');
                            $validation->setFormula1('Lookups!$C$2:$C$1000');
                            $validation->setSqref("{$hurufKolom}3:{$hurufKolom}2000");
                            break;

                        // OTOMATIS VALIDASI TANGGAL
                        case (strpos($field->field_key, 'tanggal') !== false):
                            $sheet->getStyle("{$hurufKolom}3:{$hurufKolom}2000")
                                ->getNumberFormat()
                                ->setFormatCode('yyyy-mm-dd');

                            $validation = $sheet->getCell("{$hurufKolom}3")->getDataValidation();
                            $validation->setShowInputMessage(true);
                            $validation->setPromptTitle('Format Tanggal Mandatori');
                            $validation->setPrompt('Wajib diketik: TAHUN-BULAN-HARI' . "\n" . 'Contoh: 2012-10-29');
                            $validation->setSqref("{$hurufKolom}3:{$hurufKolom}2000");
                            break;
                    }
                    $kolomIndex++;
                }
            }
        ];
    }
}

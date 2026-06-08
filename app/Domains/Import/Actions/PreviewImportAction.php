<?php

namespace App\Domains\Import\Actions;

use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\ImportTemplate;
use App\Models\Santri;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PreviewImport;
use Illuminate\Support\Facades\DB;

class PreviewImportAction
{
    public function execute($pondokId, $userId, $templateId, $file, $modeMissing, $modeExisting)
    {
        return DB::transaction(function () use ($pondokId, $userId, $templateId, $file, $modeMissing, $modeExisting) {
            // 1. Load template beserta fields-nya, urutkan sesuai 'order' di tabel pivot
            $template = ImportTemplate::with(['fields' => function($query) {
                    $query->orderBy('import_template_fields.order', 'asc');
                }])
                ->where('pondok_id', $pondokId)
                ->findOrFail($templateId);

            $templateFields = $template->fields;

            $pondok = \App\Models\Pondok::find($pondokId);
            $nisAutoGenerate = $pondok ? $pondok->nis_auto_generate : false;

            // 2. Baca nama-nama sheet menggunakan PhpSpreadsheet reader
            $sheetNames = [];
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getRealPath());
                $sheetNames = $spreadsheet->getSheetNames();
            } catch (\Exception $e) {
                // Fallback jika gagal membaca info sheet
            }

            // Baca seluruh sheet dari file Excel menggunakan package Maatwebsite Excel
            $sheets = Excel::toCollection(null, $file);
            if (!$sheets || $sheets->isEmpty()) {
                return null;
            }

            // Cari index sheet terbaik (Cari yang bernama 'Data', atau yang bukan 'Lookups')
            $targetIndex = null;
            if (!empty($sheetNames)) {
                foreach ($sheetNames as $idx => $name) {
                    if (strtolower(trim((string)$name)) === 'data') {
                        $targetIndex = $idx;
                        break;
                    }
                }

                if ($targetIndex === null) {
                    foreach ($sheetNames as $idx => $name) {
                        $normName = strtolower(trim((string)$name));
                        if ($normName !== 'lookups' && $normName !== 'lookup') {
                            $targetIndex = $idx;
                            break;
                        }
                    }
                }
            }

            if ($targetIndex === null) {
                $targetIndex = 0;
            }

            $rows = $sheets->get($targetIndex);

            if (!$rows || $rows->isEmpty()) {
                return null;
            }

            // 2b. Validasi Kolom & Mapping Dinamis
            $columnMapping = [];
            $skipRows = 1; // Default skip 1 row (the header)

            $firstRow = $rows->first();
            $firstRowArray = collect($firstRow)->map(fn($val) => is_null($val) ? '' : trim((string)$val))->toArray();

            $normalize = function ($str) {
                $str = strtolower((string)$str);
                return str_replace(['*', ' ', '_', '-'], '', $str);
            };

            // Get template field keys and labels
            $validKeysRawCase = [];
            $validKeys = [];
            $templateFieldsMap = [];
            foreach ($templateFields as $field) {
                $validKeysRawCase[] = $field->field_key;
                $validKeys[] = strtolower($field->field_key);
                $templateFieldsMap[strtolower($field->field_key)] = $field;
            }

            // Check how many cells in the first row match database keys exactly
            $matchingKeysCount = 0;
            foreach ($firstRowArray as $cellValue) {
                if (in_array($cellValue, $validKeysRawCase)) {
                    $matchingKeysCount++;
                }
            }

            // Build normalized labels map
            $normalizedLabelsMap = [];
            foreach ($templateFields as $field) {
                $normalizedLabelsMap[$normalize($field->label)] = $field->field_key;
                $normalizedLabelsMap[$normalize($field->field_key)] = $field->field_key;
            }

            // Mismatch Template Check:
            // If the user uploaded a file containing headers that overlap with OTHER system fields
            // but have 0 overlap with the currently selected template fields, raise an error immediately.
            $allSystemFields = \App\Models\ImportField::all();
            $allSystemKeys = $allSystemFields->pluck('field_key')->map(fn($k) => $normalize($k))->toArray();
            $allSystemLabels = $allSystemFields->pluck('label')->map(fn($l) => $normalize($l))->toArray();

            $uploadedHeaders = collect($firstRowArray)->map(fn($val) => $normalize($val))->filter()->toArray();

            $selectedOverlap = 0;
            foreach ($uploadedHeaders as $header) {
                if (in_array($header, $validKeys) || isset($normalizedLabelsMap[$header])) {
                    $selectedOverlap++;
                }
            }

            if ($selectedOverlap === 0 && !empty($uploadedHeaders)) {
                $otherSystemOverlap = 0;
                foreach ($uploadedHeaders as $header) {
                    if (in_array($header, $allSystemKeys) || in_array($header, $allSystemLabels)) {
                        $otherSystemOverlap++;
                    }
                }

                if ($otherSystemOverlap > 0) {
                    $foundColumns = collect($firstRowArray)->filter()->implode(', ');
                    throw new \Exception("Struktur kolom berkas Excel tidak sesuai dengan template '{$template->nama_template}' yang Anda pilih. Kolom yang ditemukan di Excel: [{$foundColumns}]. Silakan pilih template yang sesuai dengan berkas Anda atau unduh template yang benar.");
                }
            }

            // Determine mapping and header skip count
            $matchThreshold = min(2, count($validKeys));

            if ($matchingKeysCount >= $matchThreshold) {
                // CASE A: Exported template format with 2 header rows.
                // Row 1 (Index 0): Database keys.
                // Row 2 (Index 1): Visual labels.
                // Row 3+ (Index 2+): Data.
                foreach ($firstRowArray as $columnIndex => $cellValue) {
                    $cellKey = strtolower($cellValue);
                    if (in_array($cellKey, $validKeys)) {
                        $columnMapping[$cellKey] = $columnIndex;
                    }
                }
                $skipRows = 2;
            } else {
                // CASE B: Custom Excel format with 1 header row (either labels or keys).
                $matchingLabelsCount = 0;
                $tempMapping = [];
                foreach ($firstRowArray as $columnIndex => $cellValue) {
                    $normCell = $normalize($cellValue);
                    if (isset($normalizedLabelsMap[$normCell])) {
                        $matchingLabelsCount++;
                        $tempMapping[$normalizedLabelsMap[$normCell]] = $columnIndex;
                    }
                }

                if ($matchingLabelsCount >= $matchThreshold) {
                    $columnMapping = $tempMapping;
                    $skipRows = 1;
                } else {
                    // CASE C: Fallback to position-based mapping, skip 1 row.
                    foreach ($templateFields as $columnIndex => $field) {
                        $columnMapping[$field->field_key] = $columnIndex;
                    }
                    $skipRows = 1;
                }
            }

            // 3. Buat Master Batch Import
            $batch = ImportBatch::create([
                'pondok_id'         => $pondokId,
                'template_id'       => $templateId,
                'uploaded_by'       => $userId,
                'filename'          => $file->getClientOriginalName(),
                'mode_missing_nis'  => $modeMissing,
                'mode_existing_nis' => $modeExisting,
            ]);

            $total = 0;
            $valid = 0;
            $invalid = 0;
            $rowsToInsert = [];
            $seenNis = [];

            // Skip baris header
            foreach ($rows->skip($skipRows) as $index => $row) {
                $isRowEmpty = collect($row)->filter(fn($value) => !is_null($value) && trim($value) !== '')->isEmpty();
                if ($isRowEmpty) {
                    continue; // Lewati baris kosong, jangan dihitung sebagai row
                }
                $total++;

                $payload = [];
                $errors = [];
                $mode = null;

                // 4. Petakan data Excel ke Payload berdasarkan mapping kolom
                foreach ($templateFields as $field) {
                    $columnIndex = $columnMapping[$field->field_key] ?? null;
                    $value = ($columnIndex !== null && isset($row[$columnIndex])) ? $row[$columnIndex] : null;

                    // Bersihkan data text dari spasi ghaib di Excel & hapus leading single quote jika ada
                    if (is_string($value)) {
                        $value = trim($value);
                        if (str_starts_with($value, "'")) {
                            $value = substr($value, 1);
                        }
                        if ($value === '-' || strtolower($value) === 'null') {
                            $value = null;
                        }
                    }

                    // Paksa string untuk nomor panjang (NIS, NIK, No HP) agar tidak berubah jadi format scientific/float
                    if (is_numeric($value)) {
                        $valueStr = (string) $value;
                        if (strpos(strtolower($valueStr), 'e') !== false) {
                            $value = number_format((float)$value, 0, '', '');
                        } elseif (is_float($value) && $value == (int)$value) {
                            $value = (string)(int)$value;
                        } else {
                            $value = $valueStr;
                        }
                    }

                    // Normalize phone numbers
                    if (($field->field_key === 'no_hp' || $field->field_key === 'wali_no_hp') && !empty($value)) {
                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$value);
                        if (str_starts_with($cleanPhone, '628')) {
                            $value = '0' . substr($cleanPhone, 2);
                        } elseif (str_starts_with($cleanPhone, '8') && strlen($cleanPhone) >= 9 && strlen($cleanPhone) <= 12) {
                            $value = '0' . $cleanPhone;
                        } else {
                            $value = $cleanPhone;
                        }
                    }

                    // Normalize date values
                    if (in_array($field->field_key, ['tanggal_lahir', 'tanggal_masuk', 'tanggal_keluar']) && !empty($value)) {
                        if ($value instanceof \DateTimeInterface) {
                            $value = $value->format('Y-m-d');
                        } else {
                            $valueStr = trim((string)$value);
                            $parsedDate = null;

                            // Check for Excel serial number
                            if (is_numeric($valueStr) && (float)$valueStr > 1 && (float)$valueStr < 100000) {
                                try {
                                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$valueStr);
                                    $parsedDate = $dt->format('Y-m-d');
                                } catch (\Exception $e) {
                                }
                            }

                            if (!$parsedDate) {
                                $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'd M Y', 'd F Y'];
                                foreach ($formats as $format) {
                                    try {
                                        $parsed = \Carbon\Carbon::createFromFormat($format, $valueStr);
                                        if ($parsed && $parsed->format($format) === $valueStr) {
                                            $parsedDate = $parsed->format('Y-m-d');
                                            break;
                                        }
                                    } catch (\Exception $e) {
                                    }
                                }
                            }

                            if (!$parsedDate) {
                                try {
                                    $parsed = \Carbon\Carbon::parse($valueStr);
                                    $parsedDate = $parsed->format('Y-m-d');
                                } catch (\Exception $e) {
                                }
                            }

                            $value = $parsedDate ?: 'invalid';
                        }
                    }

                    // Masukkan ke payload menggunakan field_key database asli (misal: 'nama_santri', 'nis')
                    $payload[$field->field_key] = $value;
                }

                // 5. Validasi Aturan Field Dinamis & Relasional
                
                // Check dynamic required fields (configured in the database)
                foreach ($templateFields as $field) {
                    if ($field->is_required && (!isset($payload[$field->field_key]) || trim($payload[$field->field_key]) === '')) {
                        if ($field->field_key === 'nis' && $nisAutoGenerate) {
                            continue;
                        }
                        $errors[] = "Kolom {$field->label} wajib diisi.";
                    }
                }

                // Check if any Santri fields are filled
                $hasSantriData = !empty($payload['nama_lengkap']) || 
                                 !empty($payload['jenis_kelamin']) || 
                                 !empty($payload['tempat_lahir']) || 
                                 !empty($payload['tanggal_lahir']) || 
                                 !empty($payload['alamat']) || 
                                 !empty($payload['no_hp']) || 
                                 !empty($payload['status']);

                if ($hasSantriData || array_key_exists('nis', $payload)) {
                    if (empty($payload['nis']) && !$nisAutoGenerate) {
                        $errors[] = 'NIS (Nomor Induk Santri) wajib diisi untuk memasukkan data Santri.';
                    }
                }

                // Check duplicate NIS inside the Excel file
                if (!empty($payload['nis'])) {
                    if (in_array($payload['nis'], $seenNis)) {
                        $errors[] = 'NIS ganda ditemukan dalam file Excel.';
                    } else {
                        $seenNis[] = $payload['nis'];
                    }
                }

                // Kamar dependency: Kamar needs Komplek
                $hasKamarData = !empty($payload['kamar']) || !empty($payload['kapasitas_kamar']);
                if ($hasKamarData) {
                    if (!array_key_exists('komplek', $payload) || empty($payload['komplek'])) {
                        $errors[] = 'Kolom Komplek harus diisi di Excel jika kolom Kamar diisi.';
                    }
                }

                // Lemari dependency: Lemari needs Kamar
                $hasLemariData = !empty($payload['lemari']) || !empty($payload['lemari_tipe']) || !empty($payload['jumlah_slot']);
                if ($hasLemariData) {
                    if (!array_key_exists('kamar', $payload) || empty($payload['kamar'])) {
                        $errors[] = 'Kolom Kamar harus diisi di Excel jika kolom Lemari diisi.';
                    }
                }

                // LemariSlot dependency: Slot needs Lemari
                $hasSlotData = !empty($payload['slot']) || !empty($payload['slot_status']) || !empty($payload['slot_keterangan']);
                if ($hasSlotData) {
                    if (!array_key_exists('lemari', $payload) || empty($payload['lemari'])) {
                        $errors[] = 'Kolom Lemari harus diisi di Excel jika kolom Slot diisi.';
                    }
                }

                // Validate jenis_kelamin enum
                if (!empty($payload['jenis_kelamin'])) {
                    $jk = strtoupper($payload['jenis_kelamin']);
                    if ($jk !== 'L' && $jk !== 'P') {
                        $errors[] = 'Jenis Kelamin harus berupa L (Laki-laki) atau P (Perempuan).';
                    } else {
                        $payload['jenis_kelamin'] = $jk;
                    }
                }

                // Validate status enum
                if (!empty($payload['status'])) {
                    $statusVal = strtolower($payload['status']);
                    $allowedStatus = ['active', 'nonaktif', 'lulus', 'keluar', 'izin'];
                    if (!in_array($statusVal, $allowedStatus)) {
                        $errors[] = 'Status Santri harus berupa salah satu dari: active, nonaktif, lulus, keluar, izin.';
                    } else {
                        $payload['status'] = $statusVal;
                    }
                }

                // Validate NIK format
                if (!empty($payload['wali_nik'])) {
                    if (!preg_match('/^[0-9]{16}$/', $payload['wali_nik'])) {
                        $errors[] = 'NIK Wali harus berupa 16 digit angka.';
                    }
                }

                // Validate phone formats
                if (!empty($payload['no_hp'])) {
                    if (!preg_match('/^08[0-9]{8,13}$/', $payload['no_hp'])) {
                        $errors[] = 'No HP Santri harus berupa nomor ponsel Indonesia yang valid (dimulai dengan 08, 10-15 digit).';
                    }
                }
                if (!empty($payload['wali_no_hp'])) {
                    if (!preg_match('/^08[0-9]{8,13}$/', $payload['wali_no_hp'])) {
                        $errors[] = 'No HP Wali harus berupa nomor ponsel Indonesia yang valid (dimulai dengan 08, 10-15 digit).';
                    }
                }

                // Validate date formats
                foreach (['tanggal_lahir', 'tanggal_masuk', 'tanggal_keluar'] as $dateField) {
                    if (!empty($payload[$dateField])) {
                        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $payload[$dateField])) {
                            $errors[] = "Format tanggal pada kolom " . ($dateField === 'tanggal_lahir' ? 'Tanggal Lahir' : ($dateField === 'tanggal_masuk' ? 'Tanggal Masuk' : 'Tanggal Keluar')) . " tidak valid. Gunakan format YYYY-MM-DD atau DD/MM/YYYY.";
                        }
                    }
                }

                // 6. Penentuan Mode (Insert / Update / Skip) berdasarkan NIS
                if ($hasSantriData || array_key_exists('nis', $payload)) {
                    if (!empty($payload['nis'])) {
                        $santri = Santri::where('pondok_id', $pondokId)
                            ->where('nis', $payload['nis'])
                            ->first();

                        if ($santri) {
                            if ($modeExisting === 'skip') {
                                $mode = 'skip';
                            } else {
                                $mode = 'update';
                            }
                        } else {
                            if ($modeMissing === 'create') {
                                $mode = 'insert';
                            } elseif ($modeMissing === 'skip') {
                                $mode = 'skip';
                            } else {
                                $mode = 'error';
                                $errors[] = 'NIS tidak ditemukan di database pondok ini';
                            }
                        }
                    } else {
                        if ($nisAutoGenerate) {
                            $mode = 'insert';
                        } else {
                            $mode = 'error';
                            $errors[] = 'NIS wajib diisi';
                        }
                    }
                }

                // Jika ada error lain tapi mode belum ke-set error, amankan statusnya
                if (!empty($errors)) {
                    $mode = 'error';
                }

                $isValid = empty($errors);

                // 7. Siapkan untuk bulk insert
                $rowsToInsert[] = [
                    'batch_id'   => $batch->id,
                    'row_number' => $index + 1,
                    'payload'    => json_encode($payload),
                    'errors'     => !empty($errors) ? json_encode($errors) : null,
                    'mode'       => $mode,
                    'is_valid'   => $isValid ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($isValid) {
                    $valid++;
                } else {
                    $invalid++;
                }
            }

            // 8. Eksekusi Bulk Insert
            if (!empty($rowsToInsert)) {
                foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                    ImportRow::insert($chunk);
                }
            }

            // 9. Update Counter Summary Batch
            $batch->update([
                'total_rows'   => $total,
                'valid_rows'   => $valid,
                'invalid_rows' => $invalid,
            ]);

            return $batch;
        });
    }
}
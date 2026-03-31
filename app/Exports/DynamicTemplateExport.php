<?php

namespace App\Exports;

use App\Models\Santri;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DynamicTemplateExport implements FromArray, WithHeadings
{
    protected $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function headings(): array
    {
        return $this->template->fields
            ->sortBy('pivot.order')
            ->pluck('field_key')
            ->toArray();
    }

    public function array(): array
    {

        $fields = $this->headings();

        $santris = Santri::with(['wali','kamar.komplek'])
            ->where('pondok_id', auth()->user()->pondok_id)
            ->get();

        $rows = [];

        foreach ($santris as $santri) {

            $row = [];

            foreach ($fields as $field) {

                switch ($field) {

                    case 'nis':
                        $row[] = $santri->nis;
                        break;

                    case 'nama_lengkap':
                        $row[] = $santri->nama_lengkap;
                        break;

                    case 'jenis_kelamin':
                        $row[] = $santri->jenis_kelamin;
                        break;

                    case 'alamat':
                        $row[] = $santri->alamat;
                        break;

                    case 'kelas':
                        $row[] = $santri->kelas->nama ?? '';
                        break;

                    case 'komplek':
                        $row[] = $santri->kamar->komplek->nama ?? '';
                        break;

                    case 'kamar':
                        $row[] = $santri->kamar->nama ?? '';
                        break;

                    case 'wali_nama':
                        $row[] = $santri->wali->nama ?? '';
                        break;

                    case 'wali_no_hp':
                        $row[] = $santri->wali->no_hp ?? '';
                        break;

                    case 'wali_nik':
                        $row[] = $santri->wali->nik ?? '';
                        break;

                    case 'wali_pekerjaan':
                        $row[] = $santri->wali->pekerjaan ?? '';
                        break;

                    default:
                        $row[] = '';
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
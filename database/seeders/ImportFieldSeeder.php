<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImportField;

class ImportFieldSeeder extends Seeder
{
    public function run(): void
    {

        $fields = [

            /*
            |--------------------------------------------------------------------------
            | SANTRI
            |--------------------------------------------------------------------------
            */

            [
                'field_key' => 'nis',
                'label' => 'NIS',
                'entity' => 'santri',
                'column_name' => 'nis',
                'is_required' => true
            ],

            [
                'field_key' => 'nama_lengkap',
                'label' => 'Nama Santri',
                'entity' => 'santri',
                'column_name' => 'nama_lengkap'
            ],

            [
                'field_key' => 'jenis_kelamin',
                'label' => 'Jenis Kelamin',
                'entity' => 'santri',
                'column_name' => 'jenis_kelamin'
            ],

            [
                'field_key' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'entity' => 'santri',
                'column_name' => 'tempat_lahir'
            ],

            [
                'field_key' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'entity' => 'santri',
                'column_name' => 'tanggal_lahir'
            ],

            [
                'field_key' => 'alamat',
                'label' => 'Alamat Santri',
                'entity' => 'santri',
                'column_name' => 'alamat'
            ],

            [
                'field_key' => 'no_hp',
                'label' => 'No HP Santri',
                'entity' => 'santri',
                'column_name' => 'no_hp'
            ],

            [
                'field_key' => 'status',
                'label' => 'Status Santri',
                'entity' => 'santri',
                'column_name' => 'status'
            ],


            /*
            |--------------------------------------------------------------------------
            | WALI
            |--------------------------------------------------------------------------
            */

            [
                'field_key' => 'wali_nama',
                'label' => 'Nama Wali',
                'entity' => 'wali',
                'column_name' => 'nama'
            ],

            [
                'field_key' => 'wali_nik',
                'label' => 'NIK Wali',
                'entity' => 'wali',
                'column_name' => 'nik'
            ],

            [
                'field_key' => 'wali_no_hp',
                'label' => 'No HP Wali',
                'entity' => 'wali',
                'column_name' => 'no_hp'
            ],

            [
                'field_key' => 'wali_alamat',
                'label' => 'Alamat Wali',
                'entity' => 'wali',
                'column_name' => 'alamat'
            ],

            [
                'field_key' => 'wali_pekerjaan',
                'label' => 'Pekerjaan Wali',
                'entity' => 'wali',
                'column_name' => 'pekerjaan'
            ],


            /*
            |--------------------------------------------------------------------------
            | KELAS
            |--------------------------------------------------------------------------
            */

            [
                'field_key' => 'kelas',
                'label' => 'Kelas',
                'entity' => 'kelas',
                'column_name' => 'nama'
            ],


            /*
            |--------------------------------------------------------------------------
            | KOMPLEK
            |--------------------------------------------------------------------------
            */

            [
                'field_key' => 'komplek',
                'label' => 'Komplek',
                'entity' => 'komplek',
                'column_name' => 'nama'
            ],


            /*
            |--------------------------------------------------------------------------
            | KAMAR
            |--------------------------------------------------------------------------
            */

            [
                'field_key' => 'kamar',
                'label' => 'Kamar',
                'entity' => 'kamar',
                'column_name' => 'nama'
            ],

            [
                'field_key' => 'kapasitas_kamar',
                'label' => 'Kapasitas Kamar',
                'entity' => 'kamar',
                'column_name' => 'kapasitas'
            ],


            /*
            |--------------------------------------------------------------------------
            | LEMARI
            |--------------------------------------------------------------------------
            */

            [
                'field_key' => 'lemari',
                'label' => 'Nama Lemari',
                'entity' => 'lemari',
                'column_name' => 'nama'
            ],

            [
                'field_key' => 'lemari_tipe',
                'label' => 'Tipe Lemari',
                'entity' => 'lemari',
                'column_name' => 'tipe'
            ],

            [
                'field_key' => 'jumlah_slot',
                'label' => 'Jumlah Slot Lemari',
                'entity' => 'lemari',
                'column_name' => 'jumlah_slot'
            ],


            /*
            |--------------------------------------------------------------------------
            | SLOT LEMARI
            |--------------------------------------------------------------------------
            */

            [
                'field_key' => 'slot',
                'label' => 'Nomor Slot',
                'entity' => 'lemari_slot',
                'column_name' => 'nomor_slot'
            ],

            [
                'field_key' => 'slot_status',
                'label' => 'Status Slot',
                'entity' => 'lemari_slot',
                'column_name' => 'status'
            ],

            [
                'field_key' => 'slot_keterangan',
                'label' => 'Keterangan Slot',
                'entity' => 'lemari_slot',
                'column_name' => 'keterangan'
            ],

        ];


        foreach ($fields as $field) {

            ImportField::updateOrCreate(
                ['field_key' => $field['field_key']],
                $field
            );

        }
    }
}
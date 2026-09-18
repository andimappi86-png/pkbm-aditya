<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PpdbStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('admissions.apply') || $this->user()->can('admissions.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'program_id' => 'required|exists:programs,id',
            'tahun_ajaran_id' => 'required|exists:academic_years,id',
            'nik' => 'required|string|size:16|unique:ppdbs,nik',
            'nisn' => 'nullable|string|size:10|unique:ppdbs,nisn',
            'nama_lengkap' => 'required|string|max:255',
            'nama_panggilan' => 'nullable|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|string|max:50',
            'alamat' => 'required|string|max:500',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:ppdbs,email',
            'nama_ayah' => 'required|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'no_hp_ayah' => 'nullable|string|max:20',
            'nama_ibu' => 'required|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'no_hp_ibu' => 'nullable|string|max:20',
            'nama_wali' => 'nullable|string|max:255',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'no_hp_wali' => 'nullable|string|max:20',
            'alamat_wali' => 'nullable|string|max:500',
            'dok_ktp_ayah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'dok_ktp_ibu' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'dok_kk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'dok_akta_kelahiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'dok_ijasah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'dok_skhun' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'dok_foto' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'dok_ktp_wali' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }
}

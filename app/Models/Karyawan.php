<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Karyawan extends Authenticatable
{
    use Notifiable;

    /**
     * Nama tabel di database.
     * (Laravel default-nya "karyawans" — kita paksa jadi "karyawan")
     */
    protected $table = 'karyawan';

    /**
     * Primary key tabel.
     * (Laravel default-nya "id" — kita paksa jadi "id_karyawan")
     */
    protected $primaryKey = 'id_karyawan';

    /**
     * Kolom yang boleh di-mass-assign (create/update).
     */
    protected $fillable = [
        'nama_karyawan',
        'username',
        'password_hash',
        'peran',
    ];

    /**
     * Kolom yang disembunyikan saat serialize (JSON output).
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * ⭐ KUNCI UTAMA AUTH ⭐
     *
     * Beri tahu Laravel: kolom password kita bernama `password_hash`,
     * bukan `password` (default Laravel).
     *
     * Method ini dipanggil otomatis oleh Auth::attempt().
     */
    public function getAuthPassword(): ?string
    {
        return $this->password_hash;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Helper untuk mengambil nilai setting berdasarkan key.
     *
     * @param string $key
     * @param mixed $default (Nilai default jika key tidak ditemukan)
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Helper untuk menyimpan atau mengupdate nilai setting.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setValue(string $key, $value): void
    {
        // updateOrCreate akan mencari 'key', jika ada di-update, jika tidak ada di-create.
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
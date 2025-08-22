<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class LegacyUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'tb_mas_users';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    protected $fillable = [
        'strUsername',
        'strEmail',
        'strPass',
        'intROG',
        'intProgramID',
        'dteCreated',
        'strConfirmed',
        'strReset',
    ];

    protected $hidden = [
        'strPass',
        'strReset',
        'strConfirmed',
    ];

    /**
     * Check the provided password against legacy reversible scheme or bcrypt.
     */
    public function checkPassword(string $plain): bool
    {
        $stored = (string) $this->strPass;

        // 1) If bcrypt/argon hash is stored, use password_verify
        if (\preg_match('/^\$2y\$|\$argon2i\$|\$argon2id\$/', $stored) === 1) {
            return \password_verify($plain, $stored);
        }

        // 2) Fallback to legacy reversible "salting" scheme:
        //    - original algorithm: remove first 5 and last 5 chars
        //    - take every other char starting with the first
        //    - reverse the string
        $unhashed = self::legacyUnhash($stored);

        return hash_equals($unhashed, $plain);
    }

    /**
     * Legacy unhash extracted from application/libraries/Salting.php::unhash_string
     */
    public static function legacyUnhash(string $hashed): string
    {
        if (strlen($hashed) < 10) {
            return $hashed; // too short to be legacy-hashed; return as-is
        }
        $str_hash_first = substr($hashed, 0, 5);
        $str_hash_last = substr($hashed, -5);
        $str_unhash_first = str_replace($str_hash_first, '', $hashed);
        $str_unhash = str_replace($str_hash_last, '', $str_unhash_first);

        $get_string = '';
        $inc = 0;
        $len = strlen($str_unhash);
        for ($x = 0; $x < $len; $x++) {
            if ($inc === 0) {
                $inc = 1;
                $get_string .= $str_unhash[$x];
            } else {
                $inc = 0;
            }
        }
        return strrev($get_string);
    }
}

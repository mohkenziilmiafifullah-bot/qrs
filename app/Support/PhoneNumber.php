<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalize an Indonesian phone number into the "628xxxxxxxxxx" format
     * expected by wa.me links (no '+', no spaces/dashes, no leading '0').
     *
     * Examples:
     *   "0812-3456-7890" -> "6281234567890"
     *   "+62 812 3456 7890" -> "6281234567890"
     *   "62 812 3456 7890" -> "6281234567890"
     */
    public static function sanitizeToWhatsapp(string $raw): string
    {
        // Strip everything except digits (spaces, dashes, parentheses, '+', etc).
        $digits = preg_replace('/\D/', '', $raw) ?? '';

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            // Number typed without the leading 0 (e.g. "812...").
            $digits = '62'.$digits;
        }
        // Numbers already starting with '62' (with or without an original '+') are left as-is,
        // since stripping non-digits above already removed the '+'.

        return $digits;
    }
}

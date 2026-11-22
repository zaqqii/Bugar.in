<?php

namespace App\Exceptions;

use Exception;

class CheckinException extends Exception
{
    protected string $errorCode;

    public function __construct(string $errorCode, string $message = '', int $code = 400, ?\Throwable $previous = null)
    {
        $this->errorCode = $errorCode;

        if (empty($message)) {
            $message = self::defaultMessage($errorCode);
        }

        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public static function defaultMessage(string $errorCode): string
    {
        return match ($errorCode) {
            'token_invalid' => 'QR Code tidak valid atau sudah kedaluwarsa. Silakan refresh QR Code Anda.',
            'wrong_session' => 'QR Code ini bukan untuk sesi jadwal ini.',
            'outside_window' => 'Check-in belum dibuka atau sesi telah berakhir.',
            'not_registered' => 'Peserta belum terdaftar pada sesi jadwal ini.',
            'already_checked_in' => 'Peserta sudah melakukan check-in sebelumnya.',
            'forbidden' => 'Anda tidak memiliki hak akses untuk memproses check-in ini.',
            'blocked' => 'Peserta diblokir dari komunitas ini dan tidak dapat melakukan check-in.',
            'session_cancelled' => 'Sesi jadwal ini telah dibatalkan.',
            default => 'Gagal memproses check-in.',
        };
    }
}


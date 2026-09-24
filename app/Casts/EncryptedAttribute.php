<?php

namespace App\Casts;

use App\Services\AesGcmService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class EncryptedAttribute implements CastsAttributes
{
    private AesGcmService $aesService;

    public function __construct()
    {
        $this->aesService = app(AesGcmService::class);
    }

    /**
     * Cast the given value (Decrypt).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        try {
            return $this->aesService->decrypt($value);
        } catch (\Exception $e) {
            // Return raw value or handle as needed if it wasn't encrypted (e.g. legacy data)
            if (! self::isEncryptedPayload($value)) {
                return $value;
            }
            throw $e;
        }
    }

    /**
     * Prepare the given value for storage (Encrypt).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->aesService->encrypt((string) $value);
    }

    public static function isEncryptedPayload($string): bool
    {
        if (! is_string($string)) {
            return false;
        }
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            return false;
        }
        json_decode($decoded);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

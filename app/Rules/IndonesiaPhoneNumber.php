<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class IndonesiaPhoneNumber implements ValidationRule
{
    protected const PATTERN = '/^(?:\+62|62|0)[0-9]{8,15}$/';

    public function __construct(
        protected ?string $message = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail($this->message());

            return;
        }

        if (preg_match(self::PATTERN, (string) $value) !== 1) {
            $fail($this->message());
        }
    }

    protected function message(): string
    {
        return $this->message
            ?? 'Nomor telepon harus berupa nomor yang valid, diawali 0, 62, atau +62 dan hanya berisi angka setelah kode awal.';
    }
}

<?php

use App\Rules\IndonesiaPhoneNumber;

test('passes valid indonesian phone numbers', function (string $number): void {
    $rule = new IndonesiaPhoneNumber;
    $failed = false;

    $rule->validate('phone', $number, function () use (&$failed): void {
        $failed = true;
    });

    expect($failed)->toBeFalse();
})->with([
    '+6281234567890',
    '6281234567890',
    '081234567890',
    '081234567890123',
    '+62812345678901',
    '628123456789012',
    '02123456789',
]);

test('fails invalid phone numbers', function (string $number): void {
    $rule = new IndonesiaPhoneNumber;
    $failed = false;

    $rule->validate('phone', $number, function () use (&$failed): void {
        $failed = true;
    });

    expect($failed)->toBeTrue();
})->with([
    '12345',
    '08123',
    '+621234',
    'phone-number',
    '+628abc4567890',
    '0812 3456 7890',
]);

test('fails non-string and non-numeric values', function (mixed $value): void {
    $rule = new IndonesiaPhoneNumber;
    $failed = false;

    $rule->validate('phone', $value, function () use (&$failed): void {
        $failed = true;
    });

    expect($failed)->toBeTrue();
})->with([
    null,
    true,
    new stdClass,
]);

test('uses custom error message', function (): void {
    $rule = new IndonesiaPhoneNumber('Custom error message');
    $actualMessage = null;

    $rule->validate('phone', 'invalid', function (string $message) use (&$actualMessage): void {
        $actualMessage = $message;
    });

    expect($actualMessage)->toBe('Custom error message');
});

test('uses default error message', function (): void {
    $rule = new IndonesiaPhoneNumber;
    $actualMessage = null;

    $rule->validate('phone', 'invalid', function (string $message) use (&$actualMessage): void {
        $actualMessage = $message;
    });

    expect($actualMessage)->toBe('Nomor telepon harus berupa nomor yang valid, diawali 0, 62, atau +62 dan hanya berisi angka setelah kode awal.');
});

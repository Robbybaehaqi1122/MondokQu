<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Encrypt existing plaintext PII.
        // Use DB::table() directly to bypass the model's 'encrypted' cast,
        // otherwise reading plaintext data through the cast would throw DecryptException.

        $tables = [
            'santris' => ['father_phone', 'mother_phone', 'guardian_phone_number', 'emergency_contact', 'address', 'guardian_address'],
            'users' => ['phone_number'],
            'tenants' => ['contact_email', 'contact_phone_number'],
        ];

        foreach ($tables as $table => $fields) {
            DB::table($table)->orderBy('id')->chunk(100, function ($rows) use ($fields, $table) {
                foreach ($rows as $row) {
                    $data = [];
                    foreach ($fields as $field) {
                        $value = $row->$field;
                        if ($value !== null && $value !== '' && !str_starts_with($value, 'eyJ')) {
                            $data[$field] = Crypt::encryptString($value);
                        }
                    }
                    if ($data !== []) {
                        DB::table($table)->where('id', $row->id)->update($data);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'santris' => ['father_phone', 'mother_phone', 'guardian_phone_number', 'emergency_contact', 'address', 'guardian_address'],
            'users' => ['phone_number'],
            'tenants' => ['contact_email', 'contact_phone_number'],
        ];

        foreach ($tables as $table => $fields) {
            DB::table($table)->orderBy('id')->chunk(100, function ($rows) use ($fields, $table) {
                foreach ($rows as $row) {
                    $data = [];
                    foreach ($fields as $field) {
                        $value = $row->$field;
                        if ($value !== null && $value !== '' && str_starts_with($value, 'eyJ')) {
                            try {
                                $data[$field] = Crypt::decryptString($value);
                            } catch (\Exception) {
                                // already plaintext
                            }
                        }
                    }
                    if ($data !== []) {
                        DB::table($table)->where('id', $row->id)->update($data);
                    }
                }
            });
        }
    }
};

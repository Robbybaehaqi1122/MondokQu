<?php

namespace App\Http\Requests\Attendance;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Santri;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAttendanceRecordsRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'attendanceRecords';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var AttendanceSession|null $attendanceSession */
        $attendanceSession = $this->route('attendanceSession');

        if (! $this->user()?->can('manage absensi') || ! $attendanceSession) {
            return false;
        }

        return AttendanceSession::query()
            ->visibleTo($this->user())
            ->whereKey($attendanceSession->id)
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var AttendanceSession $attendanceSession */
        $attendanceSession = $this->route('attendanceSession');

        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*.santri_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(Santri::class, 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $attendanceSession->tenant_id)
                    ->where('status', Santri::STATUS_ACTIVE)),
            ],
            'records.*.status' => ['required', 'string', Rule::in(AttendanceRecord::availableStatuses())],
            'records.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var AttendanceSession $attendanceSession */
            $attendanceSession = $this->route('attendanceSession');

            if ($attendanceSession->status === AttendanceSession::STATUS_COMPLETED) {
                $validator->errors()->add('records', 'Sesi yang sudah selesai tidak dapat diubah.');
            }
        });
    }
}

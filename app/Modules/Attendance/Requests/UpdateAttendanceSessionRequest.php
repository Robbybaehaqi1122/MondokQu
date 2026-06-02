<?php

namespace App\Modules\Attendance\Requests;

use App\Models\AttendanceActivity;
use App\Models\AttendanceSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAttendanceSessionRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'updateAttendanceSession';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage absensi') ?? false;
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
        $tenantId = $this->user()?->isSuperAdmin()
            ? $attendanceSession->tenant_id
            : $this->user()?->tenant_id;

        return [
            'attendance_activity_id' => [
                'required',
                'integer',
                Rule::exists(AttendanceActivity::class, 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'session_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(AttendanceSession::availableStatuses())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $activity = AttendanceActivity::query()
                ->visibleTo($this->user())
                ->find($this->integer('attendance_activity_id'));

            if (! $activity) {
                return;
            }

            $dayKey = strtolower(Carbon::parse($this->input('session_date'))->format('l'));

            if (! in_array($dayKey, $activity->active_days ?? [], true)) {
                $validator->errors()->add('session_date', 'Tanggal sesi tidak termasuk hari aktif kegiatan ini.');
            }

            /** @var AttendanceSession $attendanceSession */
            $attendanceSession = $this->route('attendanceSession');
            $sessionExists = AttendanceSession::query()
                ->visibleTo($this->user())
                ->whereKeyNot($attendanceSession->id)
                ->where('attendance_activity_id', $activity->id)
                ->whereDate('session_date', Carbon::parse($this->input('session_date'))->toDateString())
                ->exists();

            if ($sessionExists) {
                $validator->errors()->add('session_date', 'Sesi untuk kegiatan dan tanggal ini sudah ada.');
            }
        });
    }
}

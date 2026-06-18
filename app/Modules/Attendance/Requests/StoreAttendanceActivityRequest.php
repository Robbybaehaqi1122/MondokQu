<?php

namespace App\Modules\Attendance\Requests;

use App\Models\AttendanceActivity;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceActivityRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'createAttendanceActivity';

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
        $tenantId = $this->user()?->tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(AttendanceActivity::class)->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'active_days' => ['required', 'array', 'min:1'],
            'active_days.*' => ['required', 'string', 'distinct', Rule::in(AttendanceActivity::availableDayKeys())],
            'responsible_user_id' => [
                'nullable',
                'integer',
                $this->user()?->isSuperAdmin()
                    ? Rule::exists(User::class, 'id')
                    : Rule::exists(User::class, 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'status' => ['required', 'string', Rule::in(AttendanceActivity::availableStatuses())],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

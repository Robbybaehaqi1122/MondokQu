<?php

namespace App\Modules\LeaveRequest\Requests;

use App\Models\Santri;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveRequestRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'updateLeaveRequest';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->can('create izin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'santri_id' => [
                'required',
                Rule::exists(Santri::class, 'id')
                    ->where(fn ($query) use ($user) => $query
                        ->when(! $user?->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $user->tenant_id))
                        ->where('status', Santri::STATUS_ACTIVE)),
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}

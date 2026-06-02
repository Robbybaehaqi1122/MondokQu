<?php

namespace App\Modules\Room\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'createRoom';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage kamar') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Room::class)->where(fn ($query) => $query->where('tenant_id', $this->user()?->tenant_id)),
            ],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', 'string', Rule::in(Room::availableStatuses())],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

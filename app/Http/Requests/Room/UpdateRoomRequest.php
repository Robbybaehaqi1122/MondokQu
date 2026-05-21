<?php

namespace App\Http\Requests\Room;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'updateRoom';

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
        /** @var Room $room */
        $room = $this->route('room');
        $tenantId = $this->user()?->isSuperAdmin()
            ? $room->tenant_id
            : $this->user()?->tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Room::class)
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($room),
            ],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', 'string', Rule::in(Room::availableStatuses())],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

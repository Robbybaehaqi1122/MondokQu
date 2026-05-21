<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;

class AssignRoomSantriRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'assignRoomSantri';

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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'santri_ids' => ['required', 'array', 'min:1'],
            'santri_ids.*' => ['integer', 'distinct'],
        ];
    }
}

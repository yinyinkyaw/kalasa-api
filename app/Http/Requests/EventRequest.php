<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'event_name' => 'required',
            'image' => 'required',
            'status' => 'required',
            'location' => 'required',
            'description' => 'required',
            'opening_date' => 'required',
            'opening_time' => 'required',
            'closing_date' => 'required',
            'closing_time' => 'required',
        ];
    }
}

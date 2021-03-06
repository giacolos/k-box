<?php

namespace KBox\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as Request;

class CreateMessageRequest extends Request
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
     * @return array
     */
    public function rules()
    {
        return [
            
            'text' => 'required|string',
            'to' => 'required|array|exists:users,id',
            
        ];
    }
}

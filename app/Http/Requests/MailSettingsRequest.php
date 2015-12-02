<?php namespace KlinkDMS\Http\Requests;

use KlinkDMS\Http\Requests\Request;

class MailSettingsRequest extends Request {

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
			'pretend' => 'sometimes|required|boolean',
			'host' => 'required|regex:/^[\w\d\.]*/',
			'port' => 'required|integer',
			'encryption' => 'sometimes|required|alpha',
			'smtp_u' => 'sometimes|regex:/^[\w\d\.\-_@+]*/',
			'smtp_p' => 'sometimes|regex:/^[\w\d\.\-_@+!\?]*/',
			'from_address' => 'required|email',
			'from_name' => 'required|regex:/^[\w\d\s\.\-_@+!\?]*/',
		];
	}

}
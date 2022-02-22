<?php

namespace App\Http\Requests;

use Auth;
use Common\Core\BaseFormRequest;
use Illuminate\Validation\Rule;

class CrupdateCaptionRequest extends BaseFormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $required = $this->getMethod() === 'POST' ? 'required' : '';
        $ignore = $this->getMethod() === 'PUT' ? $this->route('caption')->id : '';
        $userId = $this->route('caption') ? $this->route('caption')->user_id : Auth::id();

        return [
            'name' => [
                $required, 'string', 'min:2',
                Rule::unique('video_captions')->where('video_id', $userId)->ignore($ignore)
            ],
            'language' => "$required|string|max:5",
            'caption_file' => "$required|file|mimes:txt",
            'video_id' => "$required|integer",
        ];
    }
}

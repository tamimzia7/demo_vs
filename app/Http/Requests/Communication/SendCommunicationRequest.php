<?php

namespace App\Http\Requests\Communication;

use App\Models\Communication;
use Illuminate\Foundation\Http\FormRequest;

class SendCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }

        return $this->user()->can('create', Communication::class);
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'string', 'in:sms,email,notice,call,meeting'],
            'content' => ['nullable', 'string', 'max:5000'],
            'notice_id' => ['nullable', 'integer', 'exists:notices,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $channel = $this->input('channel');

            if ($channel === 'notice' && empty($this->input('notice_id'))) {
                $validator->errors()->add(
                    'notice_id',
                    'The notice_id field is required when channel is notice.'
                );
            }

            if (in_array($channel, ['sms', 'email', 'notice', 'call']) && empty($this->input('content'))) {
                $validator->errors()->add(
                    'content',
                    'The content field is required for '.$channel.' channel.'
                );
            }
        });
    }
}

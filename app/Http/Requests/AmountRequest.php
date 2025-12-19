<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AmountRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'amount_minor' => ['required','integer','min:1'],
    ];
  }
}

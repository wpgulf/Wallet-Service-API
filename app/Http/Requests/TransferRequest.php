<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'from_wallet_id' => ['required','integer','min:1'],
      'to_wallet_id' => ['required','integer','min:1','different:from_wallet_id'],
      'amount_minor' => ['required','integer','min:1'],
    ];
  }
}

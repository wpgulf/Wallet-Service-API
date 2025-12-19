<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'owner_name' => ['required','string','max:190'],
      'currency' => ['required','string','size:3','regex:/^[A-Z]{3}$/'],
    ];
  }
}


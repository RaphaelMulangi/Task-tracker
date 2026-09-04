<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $user->update($data);

        return response()->json($user);
    }
}

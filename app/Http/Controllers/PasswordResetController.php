<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    public function changePassword(Request $request)
    {
        // Check data
        $request->validate([
            'current_password' => 'required|required',
            'new_password' => 'required|required',
        ]);

        // Get client verified
        $user = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'La contraseña actual no es correcta'], 400);
        }

        // Update Password and change password boolean.
        $user->update([
            'password' => Hash::make($request->new_password),
            'change_password' => false,
        ]);

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }
}

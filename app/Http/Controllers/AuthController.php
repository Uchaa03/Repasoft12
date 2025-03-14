<?php

namespace App\Http\Controllers;

use App\Mail\NewClientPassword;
use App\Models\Client;
use App\Models\Technician;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request){
        //Validation register
        $validationData = $request->validate([
            'name' => 'required|max:100', //Max 100 Characters
            'email' => 'required|email|unique:technicians', //Unique email
            'password' => 'required' //Validation password in client
        ]);

        $user = Technician::create([
            'name' => $validationData['name'],
            'email' => $validationData['email'],
            'password' => Hash::make($validationData['password'])
        ]);

        //Create token
        $token = $user->createToken('authToken')->plainTextToken;

        //Return token
        return response() -> json([
            'access_token' => $token,
            'user_type' => 'technician',
            'user' => $user
        ], 201);
    }

    public function registerClient(Request $request){
        //Validation Client
        $validationDate = $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|unique:clients',
            'phone' => 'required',
            'address' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
        ]);

        //Generate a random password to send by email
        $password = Str::password(12, true, true, false);

        $client = Client::create([
            'name' => $validationDate['name'],
            'email' => $validationDate['email'],
            'phone' => $validationDate['phone'],
            'address' => $validationDate['address'],
            'city' => $validationDate['city'],
            'postal_code' => $validationDate['postal_code'],
            'password' => Hash::make($password)
        ]);

        //Send Password with a simple mail.
        try {
            Mail::to($client->email)->send(new NewClientPassword($client, $password));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Cliente registrado, pero no se pudo enviar el correo.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response() -> json([
            'client' => $client,
        ], 201);
    }

    public function login(Request $request)
    {
        // Search tech first
        $user = Technician::where('email', $request->email)->first();
        $guard = 'technician';

        // If user is not tech sear in clients
        if (!$user) {
            $user = Client::where('email', $request->email)->first();
            $guard = 'client';
        }

        // Check credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Generate token
        $token = $user->createToken('authToken')->plainTextToken;

        // Return token with user data
        return response()->json([
            'access_token' => $token,
            'user_type' => $guard, // 'technician' o 'client' for work in vue states
            'user' => $user
        ], 201);
    }


    public function logout(Request $request)
    {
        // Delete token
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout exitoso']);
    }
}

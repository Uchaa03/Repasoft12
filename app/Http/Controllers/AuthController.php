<?php

namespace App\Http\Controllers;

use App\Mail\NewClientPassword;
use App\Models\Client;
use App\Models\Technician;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportException;


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


    public function registerClient(Request $request)
    {
        try {
            // Validación del cliente
            $validatedData = $request->validate([
                'name' => 'required|max:100',
                'email' => 'required|email|unique:clients',
                'phone' => 'required',
                'address' => 'required',
            ]);

            // Generar contraseña aleatoria
            $password = Str::password(12, true, true, false);

            // Crear cliente
            $client = Client::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'],
                'password' => Hash::make($password)
            ]);

            // Intentar enviar el correo
            try {
                Mail::to($client->email)->send(new NewClientPassword($client, $password));
            } catch (TransportException $e) {
                Log::error('Error SMTP: ' . $e->getMessage(), [
                    'exception' => $e,
                    'client_id' => $client->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Cliente registrado pero el correo no pudo ser enviado',
                    'error' => $this->parseEmailError($e->getMessage()),
                    'client' => $client->makeHidden(['password'])
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente registrado y correo enviado exitosamente',
                'client' => $client->makeHidden(['password'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Error general: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Contacte al soporte técnico'
            ], 500);
        }
    }

    private function parseEmailError($message)
    {
        // Analizar mensajes comunes de error de SendGrid
        if (str_contains($message, '401 Unauthorized')) {
            return 'Error de autenticación con el servicio de correos (API key inválida)';
        }

        if (str_contains($message, 'No route to host') || str_contains($message, 'Connection timed out')) {
            return 'Error de conexión con el servidor de correos';
        }

        if (str_contains($message, 'TLS/SSL')) {
            return 'Error de configuración de seguridad en el correo';
        }

        return 'Error desconocido al enviar el correo';
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

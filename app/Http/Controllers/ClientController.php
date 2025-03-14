<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Repair;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    //Searh client by email
    public function searchClientByEmail(Request $request)
    {
        // Check email
        $data = $request->validate([
            'email' => 'required|email'
        ]);

        // Search client by email
        $client = Client::where('email', $data['email'])->first();

        // If not foun get error 404
        if (!$client) {
            return response()->json([
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        // If ok, return client selected
        return response()->json([
            'client' => $client
        ]);
    }

    //Get reparations for client
    public function clientRepairs()
    {
        try {
            $clientId = Auth::id(); // ID del cliente autenticado

            $repairs = Repair::where('client_id', $clientId)
                ->with(['technician:id,name,email']) // Solo datos necesarios del técnico
                ->latest()
                ->get([
                    'id',
                    'reference_code',
                    'device_name',
                    'model',
                    'type',
                    'status',
                    'description',
                    'created_at',
                    'technician_id'
                ]);

            return response()->json([
                'success' => true,
                'repairs' => $repairs->map(function ($repair) {
                    return [
                        'id' => $repair->id,
                        'reference_code' => $repair->reference_code,
                        'device' => $repair->device_name,
                        'model' => $repair->model,
                        'type' => $repair->type,
                        'status' => $repair->status,
                        'description' => $repair->description,
                        'creation_date' => $repair->created_at->format('d/m/Y'),
                        'technician' => $repair->technician // Ya incluye solo id, name y email
                    ];
                })
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las reparaciones'
            ], 500);
        }
    }
}

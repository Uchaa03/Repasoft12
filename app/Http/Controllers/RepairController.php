<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    //Create Repair
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'client_id' => 'required|exists:clients,id'
        ]);

        try {
            $repair = Repair::create($validated + [
                    'technician_id' => Auth::id(),
                    'status' => 'pending'
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Reparación creada exitosamente',
                'reference_code' => $repair->reference_code
            ], 201);

        } catch (Exception $e) {
            // Manejar colisión de códigos
            if (Str::contains($e->getMessage(), 'Duplicate entry')) {
                return $this->store($request); // Reintentar
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la reparación: ' . $e->getMessage()
            ], 500);
        }
    }

    //Edit status reparation
    public function updateStatus(Request $request, $id)
    {
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($request->status, $validStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Estado no válido'
            ], 422);
        }

        try {
            Repair::where('technician_id', Auth::id())
                ->findOrFail($id)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 404);
        }
    }



    //Delete reparation
    public function delete($id)
    {
        try {
            $repair = Repair::where('technician_id', Auth::id())
                ->findOrFail($id);

            $repair->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reparación eliminada correctamente',
                'deleted_repair' => [
                    'id' => $repair->id,
                    'reference_code' => $repair->reference_code
                ]
            ]);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Reparación no encontrada o no tienes permisos'
            ], 404);
        }
    }

    //Get All reparations of one technician
    public function technicianRepairs()
    {
        $repairs = Repair::where('technician_id', Auth::id())
            ->with(['client', 'technician'])
            ->latest()
            ->get();

        return response()->json([
            'count' => $repairs->count(),
            'repairs' => $repairs->map(function ($repair) {
                return [
                    'id' => $repair->id,
                    'reference_code' => $repair->reference_code,
                    'device' => $repair->device_name,
                    'model' => $repair->model,
                    'type' => $repair->type,
                    'status' => $repair->status,
                    'description' => $repair->description,
                    'created_at' => $repair->created_at->format('d/m/Y'), // Formato día/mes/año
                    'client' => [
                        'id' => $repair->client->id,
                        'name' => $repair->client->name,
                        'email' => $repair->client->email,
                        'phone' => $repair->client->phone,
                        'address' => $repair->client->address,
                        'city' => $repair->client->city,
                        'postal_code' => $repair->client->postal_code
                    ],
                    'technician' => [
                        'id' => $repair->technician->id,
                        'name' => $repair->technician->name
                    ]
                ];
            })
        ]);
    }

    //Get last 5 repairs
    public function recentRepairs()
    {
        try {
            $repairs = Repair::where('technician_id', Auth::id())
                ->with(['client:id,name,email,phone'])
                ->latest()
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'repairs' => $repairs->map(function ($repair) {
                    return [
                        'id' => $repair->id,
                        'reference_code' => $repair->reference_code,
                        'device_name' => $repair->device_name,
                        'status' => $repair->status,
                        'created_at' => $repair->created_at->format('d/m/Y'),
                        'client' => $repair->client
                    ];
                })
            ]);

        } catch (Exception) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las reparaciones recientes'
            ], 500);
        }
    }

}

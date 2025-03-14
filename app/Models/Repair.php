<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reference_code',
        'device_name',
        'model',
        'type',
        'status',
        'description',
        'client_id',
        'technician_id'
    ];

    /**
     * Relación con el cliente
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación con el técnico
     */
    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    /**
     * Generar código de referencia automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($repair) {
            // Get client from client id
            $client = Client::find($repair->client_id);

            if ($client) {
                $repair->reference_code = self::generateReferenceCode($client->name);
            } else {
                // Erro if client no exist
                throw new \Exception("Cliente no encontrado");
            }
        });
    }


    /**
     * Lógica para generar el código
     */
    protected static function generateReferenceCode($clientName)
    {
        // Limpiar el nombre (quitar espacios y caracteres especiales)
        $cleanName = preg_replace('/[^A-Za-z]/', '', $clientName);

        // Obtener las 3 primeras letras en mayúsculas
        $prefix = strtoupper(substr($cleanName, 0, 3));

        // Generar número de 4 dígitos aleatorio
        $randomNumber = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$randomNumber}-RS";
    }
}

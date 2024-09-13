<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RevisarOrdenPago implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idTransaccion;
    /**
     * Create a new job instance.
     */
    public function __construct($idTransaccion)
    {
        $this->idTransaccion = $idTransaccion;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Ejecutando el Job de RECEPCION con idTransaccion: ' . $this->idTransaccion);

            $url = env('API_ACADEMICO') . 'padres/pago-linea/validar/' . $this->idTransaccion;

            // Validar que la URL esté bien formada
            if (empty(env('API_ACADEMICO'))) {
                throw new Exception('La URL de la API no está configurada correctamente.');
            }

            // Realizar la solicitud a la API
            $response = Http::timeout(180)->get($url, [
                'idTransaccion' => $this->idTransaccion,
            ]);

            // Manejo de respuesta fallida
            if ($response->failed()) {
                Log::error("Error de conexión al API: " . $response->status());
                throw new Exception("Error de conexión al API, JOB ProcesarTransaccion.");
            }

            $respuesta = json_decode($response->body());

            // Validar si la respuesta tiene un formato válido
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Error al decodificar la respuesta JSON: " . json_last_error_msg());
                throw new Exception("Error al decodificar la respuesta JSON.");
            }

            // Verificar si la respuesta contiene un error
            if (isset($respuesta->existeError) && $respuesta->existeError) {
                Log::error("Error en la respuesta de la API: " . $respuesta->mensaje);
                throw new Exception($respuesta->mensaje);
            }

            // Log de respuesta exitosa
            Log::info('Respuesta de la API: ', (array) $respuesta);
        } catch (Exception $e) {
            
        }
    }
}

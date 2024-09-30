<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcesarTransaccion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Número máximo de intentos
    public $tries = 3;

    // Tiempo de espera antes de reintentar (en segundos)
    // public $backoff = 5;

    protected $idTransaccion;
    protected $datosCorreo;

    /**
     * Create a new job instance.
     */
    public function __construct($idTransaccion, $datosCorreo)
    {
        $this->idTransaccion = $idTransaccion;
        $this->datosCorreo = $datosCorreo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $intento = $this->attempts();
            Log::info('Ejecutando el Job TRANSACCION con idTransaccion: ' . $this->idTransaccion);

            $url = env('API_ACADEMICO') . 'padres/pago-linea/facturar/' . $this->idTransaccion . '/' . $intento .'/'. $this->tries;

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
            // Log de error con mensaje detallado
            Log::error('Error procesando la transacción: ' . $e->getMessage(), [
                'idTransaccion' => $this->idTransaccion,
            ]);

            // Relanzar la excepción para permitir los reintentos
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('El Job falló después de varios intentos: ' . $exception->getMessage(), [
            'idTransaccion' => $this->idTransaccion,
        ]);

        // Enviar un correo en caso de fallo
        // Puedes usar Mail::to($this->datosCorreo['email'])->send(new ErrorNotificacion($this->datosCorreo));
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 10, 15]; // Retrasos progresivos
    }
}

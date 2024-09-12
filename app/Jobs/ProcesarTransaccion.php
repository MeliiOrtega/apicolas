<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            Log::info('Ejecutando el Job con idTransaccion: ' . $this->idTransaccion);

            $url = env('API_ACADEMICO') . 'padres/pago-linea/facturar/' . $this->idTransaccion;
            $response = Http::timeout(180)->get($url, [
                'idTransaccion' => $this->idTransaccion
            ]);

            if ($response->failed()) {
                // Manejo personalizado para errores controlados de la API Pepito
                throw new \Exception("Error de conexion, JOB ProcesarTransaccion");
            }

            $respuesta = json_decode($response->body());

            if ($respuesta->existeError) {
                Log::info(["error", $respuesta]);
                throw new \Exception($respuesta->mensaje);
            }

            // SEGUNDA PARTE COMPROBANTE INGRESP
            Log::info($response);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function failed(\Exception $exception):void
    {
        // Enviar correo en caso de fallo después de los reintentos
    }


    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 10, 15];
    }
}

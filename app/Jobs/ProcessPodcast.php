<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\FalloTrabajoMail; // Asegúrate de tener un Mailable configurado

class ProcesarTransaccion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Número máximo de intentos
    public $tries = 3;

    // Tiempo de espera antes de reintentar (en segundos)
    public $backoff = 4;

    // Datos que recibe el Job
    protected $codEmpresa;
    protected $idTransaccion;

    public function __construct($idTransaccion)
    {
        $this->codEmpresa = $codEmpresa;
        $this->idTransaccion = $idTransaccion;
    }

    public function handle()
    {
        try {
            // Enviar solicitud a la API "Pepito"
            $response = Http::post('https://api.pepito.com/endpoint', [
                'codEmpresa' => $this->codEmpresa,
                'idTransaccion' => $this->idTransaccion,
            ]);

            // Verificar si la respuesta fue exitosa
            if ($response->failed()) {
                // Si la respuesta tiene un JSON con error
                if ($response->json('error')) {
                    throw new \Exception('Error controlado desde la API Pepito: ' . $response->json('error'));
                }

                // Si es otro tipo de fallo no controlado
                throw new \Exception('Error no controlado al comunicarse con la API Pepito: ' . $response->body());
            }

            // Aquí procesas la respuesta exitosa, si todo va bien.
        } catch (\Exception $e) {
            // Si hay un error no controlado (fallo en la solicitud o en el servidor), se lanza la excepción
            // para que Laravel maneje los reintentos.
            \Log::warning("Intento fallido: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Manejar el fallo del Job después de 3 intentos.
     */
    public function failed(\Exception $exception)
    {
        // Enviar un correo electrónico notificando el fallo
        Mail::to('admin@tuempresa.com')->send(new FalloTrabajoMail($this->codEmpresa, $this->idTransaccion, $exception->getMessage()));

        // Registrar el error
        \Log::error("El trabajo falló definitivamente después de {$this->attempts()} intentos: " . $exception->getMessage());
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\RevisarOrdenPago;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RevisarOrdenesPagoLinea extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:revisar-ordenes-pago-linea';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa órdenes de pagos en línea para cambiar su estado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Lee las órdenes de pago pendientes y que no están en cola
            $datos = DB::connection('mongodb')
                ->collection(env('MONGO_ORDEN_TEMPORAL'))
                ->where('estadoTransaccion', 'PE')
                ->where('enCola', 0)
                ->limit(10) // Ajusta el límite según tus necesidades
                ->get();

            // Procesa cada orden de pago
            foreach ($datos as $dato) {
                Log::info('Transaccion del mongo a cola: ' . $dato['idTransaccion']);

                // Despacha el job para revisar la orden de pago
                RevisarOrdenPago::dispatch($dato['idTransaccion'])->onQueue('ordenes');
            }

            $this->info('Ordenes de pago procesadas exitosamente.');
            return Command::SUCCESS;
        } catch (Exception $e) {
            // Log de error detallado
            Log::error('Error al revisar órdenes de pago: ' . $e->getMessage());
            $this->error('Ocurrió un error al procesar las órdenes de pago.');
            return Command::FAILURE;
        }
    }
}

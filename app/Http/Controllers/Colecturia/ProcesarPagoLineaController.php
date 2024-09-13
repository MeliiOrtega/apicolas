<?php

namespace App\Http\Controllers\Colecturia;

use App\Helpers\EstadoTransaccion;
use App\Http\Controllers\Controller;
use App\Jobs\ProcesarTransaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProcesarPagoLineaController extends Controller
{
    protected $idTransaccion;
    protected $et;

    public function __construct()
    {
        $this->et = new EstadoTransaccion();
    }
    /**
     * Procesa la transacción a través de un trabajo en cola.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function procesar(Request $request){
        try {
            Log::info($request->all());
            $this->idTransaccion = $request->input('idTransaccion');
            $datosCorreo = $request->input('datosCorreo');
            Log::info([$this->idTransaccion, "LLEGO TRANSACCION A LA COLA"]);
            if (!$this->idTransaccion) {
                $this->et->existeError = true;
                $this->et->mensaje =  'No es posible continuar, falta número de transacción';
            }else{
                ProcesarTransaccion::dispatch($this->idTransaccion, $datosCorreo)->onQueue('transacciones');
            }
        } catch (\Throwable $th) {
            $this->et->existeError = true;
            $this->et->mensaje = $th->getMessage();
        }

        return response()->json($this->et);

    }
}

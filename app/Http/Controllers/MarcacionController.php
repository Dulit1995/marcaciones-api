<?php

namespace App\Http\Controllers;

use App\Models\Marcacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MarcacionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'empleado_id' => 'required|exists:empleados,id',
            'tipo_marcacion' => 'required|in:ingreso,salida,almuerzo_inicio,almuerzo_fin',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if (!$this->validarMarcacion($request->empleado_id, $request->tipo_marcacion, $request->timestamp)) {
            return response()->json(['error' => 'Invalid marcacion.'], 400);
        }

        try {
            DB::beginTransaction();

            $marcacion = Marcacion::create([
                'empleado_id' => $request->empleado_id,
                'tipo_marcacion' => $request->tipo_marcacion,
                'timestamp' => $request->timestamp,
            ]);

            DB::commit();

            return response()->json($marcacion, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create marcacion.'], 500);
        }
    }

    public function show($empleado_id)
    {
        try {
            $marcaciones = Marcacion::where('empleado_id', $empleado_id)->get();
            return response()->json($marcaciones);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve marcaciones.'], 500);
        }
    }

    private function validarMarcacion($empleadoId, $tipoMarcacion, $timestamp)
    {
        $ultimaMarcacion = Marcacion::where('empleado_id', $empleadoId)
            ->orderBy('timestamp', 'desc')
            ->first();

        if (!$ultimaMarcacion) {
            return $tipoMarcacion === 'ingreso';
        }

        switch ($tipoMarcacion) {
            case 'ingreso':
                return $ultimaMarcacion->tipo_marcacion === 'salida' || $ultimaMarcacion->tipo_marcacion === 'almuerzo_fin';
            case 'salida':
                return $ultimaMarcacion->tipo_marcacion === 'ingreso' || $ultimaMarcacion->tipo_marcacion === 'almuerzo_fin';
            case 'almuerzo_inicio':
                return $ultimaMarcacion->tipo_marcacion === 'ingreso' || $ultimaMarcacion->tipo_marcacion === 'salida';
            case 'almuerzo_fin':
                return $ultimaMarcacion->tipo_marcacion === 'almuerzo_inicio';
            default:
                return false;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Marcacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MarcacionController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Request Data:', $request->all()); // Agrega esto para depurar

        $validator = Validator::make($request->all(), [
            'empleado_id' => 'required|integer',
            'tipo_marcacion' => 'required|in:ingreso,salida,almuerzo_inicio,almuerzo_fin',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Validar marcaciones consecutivas del mismo tipo
        $ultimaMarcacion = Marcacion::where('empleado_id', $request->empleado_id)
            ->orderBy('timestamp', 'desc')
            ->first();

        if ($ultimaMarcacion && $ultimaMarcacion->tipo_marcacion === $request->tipo_marcacion) {
            return response()->json(['message' => 'No se permiten marcaciones consecutivas del mismo tipo'], 400);
        }

        // Validar almuerzo_fin después de almuerzo_inicio
        if ($request->tipo_marcacion === 'almuerzo_fin') {
            $almuerzoInicio = Marcacion::where('empleado_id', $request->empleado_id)
                ->where('tipo_marcacion', 'almuerzo_inicio')
                ->where('timestamp', '<', $request->timestamp)
                ->latest()
                ->first();

            if (!$almuerzoInicio) {
                return response()->json(['message' => 'Debe haber una marcación de almuerzo_inicio antes de almuerzo_fin'], 400);
            }

            $almuerzoFinPrevio = Marcacion::where('empleado_id', $request->empleado_id)
                ->where('tipo_marcacion', 'almuerzo_fin')
                ->where('timestamp', '<', $request->timestamp)
                ->latest()
                ->first();

            if ($almuerzoFinPrevio && $almuerzoFinPrevio->timestamp > $almuerzoInicio->timestamp) {
                return response()->json(['message' => 'No se permiten múltiples marcaciones de almuerzo_fin sin almuerzo_inicio intermedio'], 400);
            }
        }

        if ($request->tipo_marcacion === 'almuerzo_inicio') {
            $almuerzoFinPendiente = Marcacion::where('empleado_id', $request->empleado_id)
                ->where('tipo_marcacion', 'almuerzo_inicio')
                ->whereNull('almuerzo_fin_id')
                ->latest()
                ->first();
            if ($almuerzoFinPendiente) {
                return response()->json(['message' => 'No se permite marcación de almuerzo_inicio sin marcación de almuerzo_fin anterior'], 400);
            }
        }

        $marcacion = Marcacion::create($request->all());

        // Si es almuerzo_inicio, guardar el ID para la marcación de almuerzo_fin
        if ($request->tipo_marcacion === 'almuerzo_inicio') {
            $marcacion->almuerzo_fin_id = $marcacion->id;
            $marcacion->save();
        }

        return response()->json(['message' => 'Marcacion registrada correctamente'], 201);
    }

    public function show($empleado_id)
    {
        $marcaciones = Marcacion::where('empleado_id', $empleado_id)->get();

        return response()->json($marcaciones);
    }
}

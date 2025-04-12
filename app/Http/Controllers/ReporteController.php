<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marcacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class ReporteController extends Controller
{
    public function generarReporte(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'empleado_id' => 'nullable|integer',
            'formato' => 'required|in:json,csv,excel',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->startOfDay();
        $fechaFin = Carbon::parse($request->input('fecha_fin'))->endOfDay();
        $empleadoId = $request->input('empleado_id');
        $formato = $request->input('formato');

        $query = Marcacion::whereBetween('timestamp', [$fechaInicio, $fechaFin]);

        if ($empleadoId) {
            $query->where('empleado_id', $empleadoId);
        }

        $marcaciones = $query->orderBy('timestamp')->get();

        $reporte = [];
        foreach ($marcaciones as $marcacion) {
            $reporte[] = [
                'empleado_id' => $marcacion->empleado_id,
                'tipo_marcacion' => $marcacion->tipo_marcacion,
                'timestamp' => $marcacion->timestamp->format('Y-m-d H:i:s'),
            ];
        }

        if ($formato == 'csv') {
            $nombreArchivo = 'reporte_marcaciones_' . date('YmdHis') . '.csv';
            $encabezados = ['Empleado ID', 'Tipo Marcacion', 'Timestamp'];
            $output = fopen('php://temp', 'w');
            fputcsv($output, $encabezados);
            foreach ($reporte as $fila) {
                fputcsv($output, [
                    $fila['empleado_id'],
                    $fila['tipo_marcacion'],
                    $fila['timestamp'],
                ]);
            }
            rewind($output);
            $csvContenido = stream_get_contents($output);
            fclose($output);

            return Response::make($csvContenido, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            ]);
        }

        return response()->json($reporte);
    }
}

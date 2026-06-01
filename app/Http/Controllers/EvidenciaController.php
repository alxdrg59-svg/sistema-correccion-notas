<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// =====================================================
// CONTROLADOR DE EVIDENCIAS
// Sirve los archivos de evidencia almacenados en
// Google Cloud Storage (GCS). Permite ver el archivo
// en el navegador o descargarlo directamente.
// =====================================================
class EvidenciaController extends Controller
{
    // =====================================================
    // VER: Muestra el archivo de evidencia en el navegador
    // Busca el registro en la tabla evidencias, verifica
    // que el archivo exista en GCS, y lo devuelve con el
    // Content-Type correcto para que el navegador lo muestre.
    // =====================================================
    public function ver($id)
    {
        $evidencia = DB::table('evidencias')->where('id', $id)->first();
        if (!$evidencia) { abort(404); }

        $disk = Storage::disk('gcs');
        if (!$disk->exists($evidencia->archivo)) { abort(404); }

        $mimeType = $this->obtenerMimeType($evidencia->archivo);

        return response($disk->get($evidencia->archivo), 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($evidencia->archivo) . '"');
    }

    // =====================================================
    // DESCARGAR: Descarga el archivo de evidencia
    // Igual que ver(), pero usa Content-Disposition: attachment
    // para forzar la descarga en lugar de mostrarlo.
    // =====================================================
    public function descargar($id)
    {
        $evidencia = DB::table('evidencias')->where('id', $id)->first();
        if (!$evidencia) { abort(404); }

        $disk = Storage::disk('gcs');
        if (!$disk->exists($evidencia->archivo)) { abort(404); }

        $mimeType = $this->obtenerMimeType($evidencia->archivo);

        return response($disk->get($evidencia->archivo), 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . basename($evidencia->archivo) . '"');
    }

    // Determina el tipo MIME segun la extension del archivo
    private function obtenerMimeType($archivo)
    {
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'pdf'  => 'application/pdf',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}

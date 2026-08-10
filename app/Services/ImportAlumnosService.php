<?php

namespace App\Services;

use App\Models\Alumno;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportAlumnosService
{
    /**
     * Columnas obligatorias esperadas en la cabecera del archivo (CSV o Excel).
     */
    private const COLUMNAS_OBLIGATORIAS = ['Nombre', 'Apellidos'];

    /**
     * Importa alumnos desde un archivo CSV o Excel (.xlsx/.xls), deduplicando
     * por nombre + apellidos + grupo + curso académico. grupo_id y curso_academico
     * son constantes para todo el lote (vienen del formulario, no de columnas del
     * archivo). ciclo_id/numero_curso ya no se escriben aqui -- se derivan siempre
     * de grupo_id via Alumno::getCicloAttribute()/getNumeroCursoAttribute().
     *
     * @return array{success: bool, importados: int, omitidos: int, mensaje: string}
     */
    public function importar(string $rutaArchivo, int $grupoId, string $cursoAcademico): array
    {
        $filas = $this->leerFilas($rutaArchivo);

        if ($filas === null) {
            return $this->fallo('No se ha podido leer el archivo. Comprueba que el formato sea CSV, XLSX o XLS válido.');
        }

        if (empty($filas)) {
            return $this->fallo('El archivo está vacío.');
        }

        $cabecera = array_map(static fn ($valor) => trim((string) $valor), array_shift($filas));
        $indices  = $this->mapearColumnas($cabecera);

        $faltantes = array_diff(self::COLUMNAS_OBLIGATORIAS, array_keys($indices));
        if (!empty($faltantes)) {
            return $this->fallo(
                'Faltan columnas obligatorias en el archivo: ' . implode(', ', $faltantes) . '.'
            );
        }

        $importados = 0;
        $omitidos   = 0;

        foreach ($filas as $fila) {
            if ($this->filaVacia($fila)) {
                continue;
            }

            $nombre    = trim((string) ($fila[$indices['Nombre']] ?? ''));
            $apellidos = trim((string) ($fila[$indices['Apellidos']] ?? ''));

            if ($nombre === '' || $apellidos === '') {
                $omitidos++;
                continue;
            }

            $existe = Alumno::query()
                ->where('nombre', $nombre)
                ->where('apellidos', $apellidos)
                ->where('grupo_id', $grupoId)
                ->where('curso_academico', $cursoAcademico)
                ->exists();

            if ($existe) {
                $omitidos++;
                continue;
            }

            Alumno::create([
                'nombre'          => $nombre,
                'apellidos'       => $apellidos,
                'email'           => $this->valorColumna($fila, $indices, 'Email'),
                'telefono'        => $this->valorColumna($fila, $indices, 'Teléfono'),
                'grupo_id'        => $grupoId,
                'curso_academico' => $cursoAcademico,
                'importado_via'   => 'excel',
            ]);

            $importados++;
        }

        return [
            'success'    => true,
            'importados' => $importados,
            'omitidos'   => $omitidos,
            'mensaje'    => "Importación completada: {$importados} alumno(s) nuevos, {$omitidos} omitido(s) (ya existentes o con datos incompletos).",
        ];
    }

    private function fallo(string $mensaje): array
    {
        return [
            'success'    => false,
            'importados' => 0,
            'omitidos'   => 0,
            'mensaje'    => $mensaje,
        ];
    }

    /**
     * @return array<int, array<int, mixed>>|null
     */
    private function leerFilas(string $ruta): ?array
    {
        if (!is_file($ruta)) {
            return null;
        }

        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        try {
            if ($extension === 'csv') {
                return $this->leerCsv($ruta);
            }

            $spreadsheet = IOFactory::load($ruta);
            return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function leerCsv(string $ruta): array
    {
        $handle = fopen($ruta, 'r');
        if ($handle === false) {
            return [];
        }

        // Descartar BOM UTF-8 si el archivo lo lleva (típico al exportar desde Excel en Windows)
        $primerosBytes = fread($handle, 3);
        if ($primerosBytes !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $filas = [];
        while (($fila = fgetcsv($handle, 0, ',')) !== false) {
            $filas[] = $fila;
        }
        fclose($handle);

        return $filas;
    }

    /**
     * @return array<string, int>
     */
    private function mapearColumnas(array $cabecera): array
    {
        $indices = [];
        foreach ($cabecera as $i => $nombreColumna) {
            $nombreColumna = trim((string) $nombreColumna);
            if ($nombreColumna !== '') {
                $indices[$nombreColumna] = $i;
            }
        }
        return $indices;
    }

    private function filaVacia(array $fila): bool
    {
        foreach ($fila as $valor) {
            if (trim((string) $valor) !== '') {
                return false;
            }
        }
        return true;
    }

    private function valorColumna(array $fila, array $indices, string $columna): ?string
    {
        if (!isset($indices[$columna])) {
            return null;
        }
        $valor = trim((string) ($fila[$indices[$columna]] ?? ''));
        return $valor === '' ? null : $valor;
    }
}

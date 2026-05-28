<?php

namespace App\Services;

use App\Models\Direccion;

use App\Models\Empresa;
use App\Models\User;
use App\Models\CicloFormativo;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportExcelService
{
    protected array $errores = [];
    protected int $importadas = 0;
    protected int $actualizadas = 0;
    protected int $omitidas = 0;
    protected bool $auditoriaDisponible = false;

    public function importarEmpresas(string $rutaArchivo, int $creadorIdPorDefecto, bool $actualizarExistentes = false): array
    {
        $this->errores = [];
        $this->importadas = 0;
        $this->actualizadas = 0;
        $this->omitidas = 0;

        try {
            $spreadsheet = IOFactory::load($rutaArchivo);
            $worksheet = $spreadsheet->getActiveSheet();
            $filas = $worksheet->toArray();

            $encabezados = array_shift($filas);
            $mapaColumnas = $this->mapearColumnas($encabezados);

            if (empty($mapaColumnas['nombre']) || empty($mapaColumnas['cif'])) {
                return [
                    'success' => false,
                    'mensaje' => 'El archivo debe contener las columnas "Nombre" y "CIF".',
                ];
            }

            $this->auditoriaDisponible = Schema::hasTable('auditoria');
            DB::beginTransaction();

            foreach ($filas as $index => $fila) {
                $this->procesarFila($fila, $mapaColumnas, $index + 2, $creadorIdPorDefecto, $actualizarExistentes);
            }

            DB::commit();

            return [
                'success' => true,
                'importadas' => $this->importadas,
                'actualizadas' => $this->actualizadas,
                'omitidas' => $this->omitidas,
                'errores' => $this->errores,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    protected function mapearColumnas(array $encabezados): array
    {
        $mapa = [];
        $mapeo = [
            'nombre' => ['nombre', 'empresa', 'razón social', 'razon social'],
            'cif' => ['cif', 'nif', 'cif/nif'],
            'telefono' => ['teléfono', 'telefono', 'tel'],
            'email' => ['email', 'correo', 'e-mail', 'email empresa'],
            'persona_contacto' => ['contacto', 'persona contacto', 'responsable empresa'],
            'direccion' => ['dirección', 'direccion', 'domicilio'],
            'num_convenio' => ['convenio', 'nº convenio', 'num convenio'],
            'fecha_firma' => ['fecha firma', 'fecha convenio', 'fecha'],
            'notas' => ['notas', 'observaciones', 'comentarios'],
            'ciclos' => ['ciclos', 'ciclos formativos'],
            'profesor_email' => ['profesor', 'email profesor', 'profesor responsable', 'responsable', 'creador'],
        ];

        foreach ($encabezados as $index => $encabezado) {
            $enc = mb_strtolower(trim($encabezado ?? ''));
            foreach ($mapeo as $campo => $variantes) {
                if (in_array($enc, $variantes)) {
                    $mapa[$campo] = $index;
                    break;
                }
            }
        }
        return $mapa;
    }

    protected function procesarFila(array $fila, array $mapa, int $numFila, int $creadorIdPorDefecto, bool $actualizar): void
    {
        $nombre = trim($fila[$mapa['nombre']] ?? '');
        $cif = strtoupper(trim($fila[$mapa['cif']] ?? ''));

        if (empty($nombre) || empty($cif)) {
            $this->errores[] = "Fila {$numFila}: Nombre o CIF vacío.";
            $this->omitidas++;
            return;
        }

        $existente = Empresa::where('cif', $cif)->first();
        if ($existente && !$actualizar) {
            $this->errores[] = "Fila {$numFila}: CIF {$cif} ya existe.";
            $this->omitidas++;
            return;
        }

        // Determinar el creador (profesor responsable)
        $creadorId = $creadorIdPorDefecto;
        if (isset($mapa['profesor_email'])) {
            $profesorEmail = trim($fila[$mapa['profesor_email']] ?? '');
            if (!empty($profesorEmail)) {
                $profesor = User::where('email', $profesorEmail)->where('activo', true)->first();
                if ($profesor) {
                    $creadorId = $profesor->id;
                } else {
                    $this->errores[] = "Fila {$numFila}: Profesor '{$profesorEmail}' no encontrado. Se asigna al importador.";
                }
            }
        }

        $direccionTexto = trim($fila[$mapa['direccion'] ?? -1] ?? '');

        $personaContactoNombre  = trim($fila[$mapa['persona_contacto'] ?? -1] ?? '');
        $personaContactoTelefono = trim($fila[$mapa['telefono'] ?? -1] ?? '');
        $personaContactoEmail    = trim($fila[$mapa['email'] ?? -1] ?? '');

        $datos = [
            'nombre' => $nombre,
            'cif' => $cif,
            // telefono y email gestionados via tabla personas_contacto
            'num_convenio' => trim($fila[$mapa['num_convenio'] ?? -1] ?? ''),
            'notas' => trim($fila[$mapa['notas'] ?? -1] ?? ''),
        ];

        if (isset($mapa['fecha_firma']) && !empty($fila[$mapa['fecha_firma']])) {
            $datos['fecha_firma'] = $this->parsearFecha($fila[$mapa['fecha_firma']]);
        }

        // Procesar ciclos si están presentes
        $ciclosIds = [];
        if (isset($mapa['ciclos']) && !empty($fila[$mapa['ciclos']])) {
            $ciclosTexto = trim($fila[$mapa['ciclos']]);
            $ciclosCodigos = array_map('trim', preg_split('/[,;\/]/', $ciclosTexto));
            foreach ($ciclosCodigos as $codigo) {
                if (!empty($codigo)) {
                    $ciclo = CicloFormativo::where('codigo', strtoupper($codigo))->first();
                    if ($ciclo) {
                        $ciclosIds[] = $ciclo->id;
                    } else {
                        $this->errores[] = "Fila {$numFila}: Ciclo '{$codigo}' no encontrado.";
                    }
                }
            }
        }

        if ($existente) {
            $datosAnt = $existente->toArray();
            // En actualización, también actualizar el creador si se especificó
            if (isset($mapa['profesor_email']) && !empty(trim($fila[$mapa['profesor_email']] ?? ''))) {
                $datos['creador_id'] = $creadorId;
            }
            $existente->update($datos);
            if (!empty($ciclosIds)) {
                $existente->ciclos()->sync($ciclosIds);
            }
            if ($this->auditoriaDisponible) {
                Auditoria::registrarActualizacion($existente, $datosAnt, "Importación Excel");
            }
            // Crear persona de contacto si se importó y no tiene ninguna
            $hayDatosContacto = !empty($personaContactoNombre) || !empty($personaContactoTelefono) || !empty($personaContactoEmail);
            if ($hayDatosContacto && $existente->personasContacto()->count() === 0) {
                $existente->personasContacto()->create([
                    'nombre'    => !empty($personaContactoNombre) ? $personaContactoNombre : 'Contacto general',
                    'telefono'  => $personaContactoTelefono ?: null,
                    'email'     => $personaContactoEmail ?: null,
                    'principal' => true,
                ]);
            }
            // Crear dirección si se importó y la empresa no tiene ninguna
            if (!empty($direccionTexto) && $existente->direcciones()->count() === 0) {
                Direccion::create([
                    'empresa_id' => $existente->id,
                    'nombre_via' => $direccionTexto,
                    'principal'  => true,
                ]);
            }
            $this->actualizadas++;
        } else {
            $datos['creador_id'] = $creadorId;
            $empresa = Empresa::create($datos);
            if (!empty($ciclosIds)) {
                $empresa->ciclos()->sync($ciclosIds);
            }
            if ($this->auditoriaDisponible) {
                Auditoria::registrarCreacion($empresa, "Importación Excel");
            }
            // Crear persona de contacto si se importó
            if (!empty($personaContactoNombre) || !empty($personaContactoTelefono) || !empty($personaContactoEmail)) {
                $empresa->personasContacto()->create([
                    'nombre'    => !empty($personaContactoNombre) ? $personaContactoNombre : 'Contacto general',
                    'telefono'  => $personaContactoTelefono ?: null,
                    'email'     => $personaContactoEmail ?: null,
                    'principal' => true,
                ]);
            }
            // Crear dirección si se importó
            if (!empty($direccionTexto)) {
                Direccion::create([
                    'empresa_id' => $empresa->id,
                    'nombre_via' => $direccionTexto,
                    'principal'  => true,
                ]);
            }
            $this->importadas++;
        }
    }

    protected function parsearFecha($fecha): ?string
    {
        if (empty($fecha)) return null;
        if (is_numeric($fecha)) {
            return date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($fecha));
        }
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $formato) {
            $parsed = \DateTime::createFromFormat($formato, $fecha);
            if ($parsed) return $parsed->format('Y-m-d');
        }
        return null;
    }

    public function generarPlantilla(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Empresas');
        
        // Encabezados con la nueva columna de profesor
        $sheet->fromArray([
            'Nombre', 'CIF', 'Teléfono', 'Email Empresa', 'Persona Contacto', 
            'Dirección', 'Nº Convenio', 'Fecha Firma', 'Ciclos', 'Email Profesor', 'Notas'
        ], null, 'A1');
        
        // Fila de ejemplo
        $sheet->fromArray([
            'Empresa Ejemplo S.L.', 'B12345678', '912345678', 'info@ejemplo.es', 'Juan Pérez', 
            'Calle Mayor 1, Madrid', 'CONV-2024-001', '15/01/2024', 'DAM, DAW', 'profesor@educa.madrid.org', 'Empresa del sector tecnológico'
        ], null, 'A2');
        
        // Segunda fila de ejemplo
        $sheet->fromArray([
            'Otra Empresa S.A.', 'A87654321', '913456789', 'contacto@otra.es', 'María López', 
            'Av. Principal 50', 'CONV-2024-002', '20/02/2024', 'STI', 'otro.profesor@educa.madrid.org', ''
        ], null, 'A3');
        
        // Estilo de encabezados
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
        ]);

        // Ajustar ancho de columnas
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Añadir hoja de ayuda
        $ayuda = $spreadsheet->createSheet()->setTitle('Ayuda');
        $ayuda->fromArray([
            ['Campo', 'Obligatorio', 'Descripción', 'Ejemplo'],
            ['Nombre', 'Sí', 'Razón social de la empresa', 'Empresa Ejemplo S.L.'],
            ['CIF', 'Sí', 'CIF o NIF de la empresa', 'B12345678'],
            ['Teléfono', 'No', 'Teléfono de contacto', '912345678'],
            ['Email Empresa', 'No', 'Correo electrónico de la empresa', 'info@ejemplo.es'],
            ['Persona Contacto', 'No', 'Nombre del contacto en la empresa', 'Juan Pérez'],
            ['Dirección', 'No', 'Dirección completa', 'Calle Mayor 1, Madrid'],
            ['Nº Convenio', 'No', 'Número de convenio asignado', 'CONV-2024-001'],
            ['Fecha Firma', 'No', 'Fecha de firma del convenio (DD/MM/AAAA)', '15/01/2024'],
            ['Ciclos', 'No', 'Códigos de ciclos separados por coma', 'DAM, DAW, STI'],
            ['Email Profesor', 'No', 'Email del profesor responsable (debe existir en el sistema)', 'profesor@educa.madrid.org'],
            ['Notas', 'No', 'Observaciones adicionales', 'Cualquier comentario'],
        ], null, 'A1');
        
        $ayuda->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
        ]);
        
        foreach (range('A', 'D') as $col) {
            $ayuda->getColumnDimension($col)->setAutoSize(true);
        }

        // Volver a la primera hoja
        $spreadsheet->setActiveSheetIndex(0);

        $path = storage_path('app/plantilla_importacion.xlsx');
        (new Xlsx($spreadsheet))->save($path);
        return $path;
    }
}

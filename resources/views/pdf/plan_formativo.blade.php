<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #000; }
    .page { padding: 20mm 15mm 15mm 15mm; }

    h1 { font-size: 11pt; font-weight: bold; text-align: center; margin-bottom: 2px; }
    h2 { font-size: 10pt; font-weight: bold; text-align: center; margin-bottom: 8px; }
    .subtitulo { font-size: 9pt; text-align: center; margin-bottom: 4px; }
    .regimen-fecha { text-align: center; margin-bottom: 10px; font-size: 9pt; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    td, th { border: 1px solid #000; padding: 3px 5px; font-size: 9pt; }
    .label { font-weight: bold; background: #f0f0f0; width: 30%; }
    .no-border td { border: none; }
    .header-cell { font-weight: bold; }

    .seccion-titulo { font-weight: bold; border: 1px solid #000; padding: 3px 5px;
                      background: #e0e0e0; margin-top: 6px; margin-bottom: 0; }
    .firma-area { margin-top: 20px; }
    .firma-col { display: inline-block; width: 30%; text-align: center; vertical-align: top; }

    .checkbox { border: 1px solid #000; display: inline-block; width: 10px; height: 10px; margin-right: 4px; }
    .check-marcado { background: #000; }

    .ra-table th { background: #d0d0d0; font-size: 8pt; text-align: center; }
    .ra-table td { font-size: 8pt; }

    .periodo-table th { background: #d0d0d0; text-align: center; font-size: 8pt; }

    .logo-area { float: left; width: 18mm; }
    .logo-area img { width: 16mm; }
    .header-text { margin-left: 20mm; }
</style>
</head>
<body>
<div class="page">

    {{-- CABECERA --}}
    <h1>Anexo</h1>
    <h2>Plan de formación en empresa u organismo equiparado</h2>

    <table>
        <tr>
            <td colspan="2" style="text-align:center; font-weight:bold; background:#e8e8e8; padding:4px;">
                PLAN DE FORMACIÓN<br>
                <span style="font-weight:normal; font-size:8.5pt;">
                    Resultados de aprendizaje en periodos de formación en empresa u organismo equiparado
                </span>
            </td>
        </tr>
        <tr>
            <td style="border:none; text-align:center; font-size:9pt;" colspan="2">
                Régimen: <u>General</u> &nbsp;&nbsp;&nbsp;
                Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y') }} &nbsp;/&nbsp;
                Curso {{ $asignacion->curso_academico }}
            </td>
        </tr>
    </table>

    {{-- CICLO --}}
    <table>
        <tr>
            <td colspan="2" class="label">Ciclo Formativo / Curso de especialización / Programa de especialización:</td>
        </tr>
        <tr>
            <td>{{ $asignacion->ciclo->nombre ?? '' }}</td>
            <td style="width:40%;">Código: {{ $asignacion->ciclo->codigo ?? '' }} &nbsp;&nbsp; Curso: {{ $asignacion->numero_curso }}º</td>
        </tr>
    </table>

    {{-- ALUMNO --}}
    <table>
        <tr>
            <td colspan="2" class="label">Alumno/a:</td>
        </tr>
        <tr>
            <td colspan="2">{{ $asignacion->alumno->apellidos ?? '' }}, {{ $asignacion->alumno->nombre ?? '' }}</td>
        </tr>
        <tr>
            <td>Correo electrónico: {{ $asignacion->alumno->email ?? '' }}</td>
            <td>Teléfono: {{ $asignacion->alumno->telefono ?? '' }}</td>
        </tr>
    </table>

    {{-- CENTRO --}}
    <table>
        <tr>
            <td colspan="2" class="label">Centro docente:</td>
        </tr>
        <tr>
            <td colspan="2">{{ $centro['nombre'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Correo electrónico: {{ $centro['email'] ?? '' }}</td>
            <td>Teléfono: {{ $centro['telefono'] ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2">Tutor/a del centro de formación: {{ $asignacion->tutorIes->nombre ?? '' }}</td>
        </tr>
        <tr>
            <td>Correo electrónico: {{ $asignacion->tutorIes->email ?? '' }}</td>
            <td>Teléfono: </td>
        </tr>
    </table>

    {{-- EMPRESA --}}
    <table>
        <tr>
            <td class="label">Empresa u organismo equiparado:</td>
            <td>N.I.F.: {{ $asignacion->empresa->cif ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2">{{ $asignacion->empresa->nombre ?? '' }}</td>
        </tr>
        <tr>
            <td>Correo electrónico: {{ $asignacion->empresa->email ?? '' }}</td>
            <td>Teléfono: {{ $asignacion->empresa->telefono ?? '' }}</td>
        </tr>
        @if($asignacion->tutorEmpresa)
        <tr>
            <td colspan="2">Tutor/a de empresa: {{ $asignacion->tutorEmpresa->apellidos ?? '' }}, {{ $asignacion->tutorEmpresa->nombre ?? '' }}</td>
        </tr>
        <tr>
            <td>Correo electrónico: {{ $asignacion->tutorEmpresa->email ?? '' }}</td>
            <td>Teléfono: {{ $asignacion->tutorEmpresa->telefono ?? '' }}</td>
        </tr>
        @else
        <tr><td colspan="2">Tutor/a de empresa u organismo equiparado:</td></tr>
        <tr><td>Correo electrónico:</td><td>Teléfono:</td></tr>
        @endif
    </table>

    {{-- ADAPTACIONES --}}
    <table>
        <tr>
            <td style="width:70%;">Requiere medidas/adaptaciones extraordinarias por discapacidad:</td>
            <td style="width:15%; text-align:center;">SÍ <span class="checkbox"></span></td>
            <td style="width:15%; text-align:center;">NO <span class="checkbox check-marcado"></span></td>
        </tr>
        <tr>
            <td colspan="3" style="height:12mm; font-size:8pt; color:#555;">
                &nbsp;&nbsp;En caso afirmativo, especificar medidas/adaptaciones:
            </td>
        </tr>
        <tr>
            <td style="width:70%;">Requiere autorización extraordinaria:</td>
            <td style="text-align:center;">SÍ <span class="checkbox"></span></td>
            <td style="text-align:center;">NO <span class="checkbox check-marcado"></span></td>
        </tr>
        <tr>
            <td colspan="3" style="height:12mm; font-size:8pt; color:#555;">
                &nbsp;&nbsp;Indicar causa/s:
            </td>
        </tr>
    </table>

    {{-- INTERVALO --}}
    <table>
        <tr>
            <td>Intervalo de formación:</td>
            <td style="text-align:center;">Diario <span class="checkbox"></span></td>
            <td style="text-align:center;">Semanal <span class="checkbox"></span></td>
            <td style="text-align:center;">Mensual <span class="checkbox"></span></td>
            <td style="text-align:center;">Otros <span class="checkbox"></span></td>
            <td style="text-align:center;">Varias empresas <span class="checkbox"></span></td>
        </tr>
    </table>

    {{-- PERIODOS --}}
    <table class="periodo-table">
        <tr>
            <td colspan="3" style="font-weight:bold; background:#e8e8e8;">Periodos de formación en empresa u organismo equiparado:</td>
        </tr>
        <tr>
            <th style="width:15%;">Nº periodo</th>
            <th style="width:50%;">Calendario</th>
            <th style="width:35%;">Horario</th>
        </tr>
        @if($asignacion->fecha_inicio && $asignacion->fecha_fin)
        <tr>
            <td style="text-align:center;">1</td>
            <td>{{ $asignacion->fecha_inicio->format('d/m/Y') }} – {{ $asignacion->fecha_fin->format('d/m/Y') }}</td>
            <td>{{ $asignacion->horario ?? '' }}</td>
        </tr>
        @else
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        @endif
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr>
            <td colspan="2" style="text-align:right; font-weight:bold;">Total horas:</td>
            <td>{{ $asignacion->num_horas ?? '' }}</td>
        </tr>
    </table>

    {{-- OBSERVACIONES --}}
    <table>
        <tr>
            <td style="height:14mm; vertical-align:top;">Observaciones:&nbsp;</td>
        </tr>
    </table>

    {{-- PÁGINA 2: RA/CE por módulo --}}
    <div style="page-break-before: always; padding-top:10mm;">
        <table class="ra-table">
            <tr>
                <th style="width:35%;">Módulo profesional (Denominación)</th>
                <th style="width:10%;">Código</th>
                <th style="width:30%;">Resultados de Aprendizaje</th>
                <th style="width:12%;">Íntegramente en empresa</th>
                <th style="width:13%;">Compartida con centro</th>
            </tr>
            @php
                $raAgrupados = $asignacion->resultadosAprendizaje->groupBy(fn($ra) => $ra->modulo_id ?? 0);
            @endphp
            @foreach($raAgrupados as $moduloId => $ras)
                @php $modulo = $ras->first()->modulo; @endphp
                @foreach($ras as $i => $ra)
                <tr>
                    @if($i === 0)
                    <td rowspan="{{ $ras->count() }}">{{ $modulo->nombre ?? '' }}</td>
                    <td rowspan="{{ $ras->count() }}" style="text-align:center;">{{ $modulo->codigo ?? '' }}</td>
                    @endif
                    <td>RA{{ $ra->numero ?? ($i+1) }}: {{ $ra->descripcion ?? $ra->nombre ?? '' }}</td>
                    <td style="text-align:center;">&nbsp;</td>
                    <td style="text-align:center;">X</td>
                </tr>
                @endforeach
            @endforeach
            @if($asignacion->resultadosAprendizaje->isEmpty())
            <tr>
                <td colspan="5" style="text-align:center; color:#999; font-style:italic;">
                    Sin resultados de aprendizaje asignados
                </td>
            </tr>
            @endif
        </table>

        <table style="margin-top:6px;">
            <tr>
                <td style="font-style:italic; font-size:8pt; border:none;">
                    (*) Indicar el número que corresponde al resultado de aprendizaje según lo establecido en el real decreto que establece el título correspondiente.
                </td>
            </tr>
        </table>

        <table style="margin-top:8px;">
            <tr>
                <td style="height:12mm; vertical-align:top;">
                    Formaciones específicas y no vinculadas al currículo:
                </td>
            </tr>
        </table>

        {{-- FIRMAS --}}
        <table style="margin-top:20mm; border:none;">
            <tr>
                <td style="border:none; text-align:center; width:33%;">
                    Fdo.: Tutor/a del centro docente<br><br><br>
                    _______________________<br>
                    <small>{{ $asignacion->tutorIes->nombre ?? '' }}</small>
                </td>
                <td style="border:none; text-align:center; width:33%;">
                    Fdo.: Tutor/a de la empresa<br><br><br>
                    _______________________<br>
                    <small>{{ optional($asignacion->tutorEmpresa)->nombre ?? '' }}</small>
                </td>
                <td style="border:none; text-align:center; width:33%;">
                    Fdo.: Alumno/a<br><br><br>
                    _______________________<br>
                    <small>{{ $asignacion->alumno->nombre ?? '' }}</small>
                </td>
            </tr>
        </table>

        <table style="margin-top:10px; border:none;">
            <tr>
                <td style="border:none; font-size:8pt;">
                    Periodo: {{ $asignacion->fecha_inicio?->format('d/m/Y') ?? '___' }} a {{ $asignacion->fecha_fin?->format('d/m/Y') ?? '___' }}
                </td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>

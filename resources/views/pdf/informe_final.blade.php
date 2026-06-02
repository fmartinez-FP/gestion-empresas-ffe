<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #000; }
    .page { padding: 18mm 15mm 15mm 15mm; }
    h1 { font-size: 11pt; font-weight: bold; text-align: center; margin-bottom: 2px; }
    h2 { font-size: 10pt; font-weight: bold; text-align: center; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    td, th { border: 1px solid #000; padding: 3px 5px; font-size: 9pt; }
    .label { font-weight: bold; background: #e0e0e0; }
    .area-grande { height: 20mm; vertical-align: top; }
    .checkbox { border: 1px solid #000; display: inline-block; width: 9px; height: 9px; margin: 0 3px; }
    .ra-table th { background: #d0d0d0; text-align: center; font-size: 8pt; }
</style>
</head>
<body>
<div class="page">

    <h1>Anexo</h1>
    <h2>Informe de valoración final del tutor de la empresa u organismo equiparado</h2>

    {{-- CABECERA --}}
    <table style="margin-bottom:10px; width:60%; margin-left:auto; margin-right:auto;">
        <tr>
            <th style="text-align:center;">Curso académico</th>
            <th style="text-align:center;">Nº del Convenio o Acuerdo <sup>(2)</sup></th>
            <th style="text-align:center;">Nº del Anexo Relación de alumnos<sup>(3)</sup></th>
        </tr>
        <tr>
            <td style="text-align:center;">{{ $asignacion->curso_academico }}</td>
            <td style="text-align:center;">&nbsp;</td>
            <td style="text-align:center;">&nbsp;</td>
        </tr>
    </table>

    {{-- DATOS ALUMNO --}}
    <table>
        <tr><td colspan="2" class="label">Datos del alumno</td></tr>
        <tr>
            <td style="width:60%;">Apellidos: {{ $asignacion->alumno->apellidos ?? '' }}</td>
            <td>Nombre: {{ $asignacion->alumno->nombre ?? '' }}</td>
        </tr>
        <tr><td colspan="2">e-mail de contacto: {{ $asignacion->alumno->email ?? '' }}</td></tr>
    </table>

    {{-- DATOS EMPRESA --}}
    <table>
        <tr><td colspan="2" class="label">Datos del centro de trabajo</td></tr>
        <tr><td colspan="2">DENOMINACIÓN: {{ $asignacion->empresa->nombre ?? '' }}</td></tr>
        <tr><td colspan="2" class="label" style="font-style:italic;">Tutor de la empresa u organismo equiparado</td></tr>
        <tr>
            <td>Apellidos: {{ optional($asignacion->tutorEmpresa)->apellidos ?? '' }}</td>
            <td>Nombre: {{ optional($asignacion->tutorEmpresa)->nombre ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2">E-mail del tutor empresa u organismo equiparado: {{ optional($asignacion->tutorEmpresa)->email ?? '' }}</td>
        </tr>
    </table>

    {{-- DATOS ESTUDIOS --}}
    <table>
        <tr><td colspan="3" class="label">Datos de los estudios realizados</td></tr>
        <tr>
            <td colspan="3">DENOMINACIÓN completa del ciclo formativo / curso de especialización / programa profesional:<br>
                {{ $asignacion->ciclo->nombre ?? '' }}
            </td>
        </tr>
        <tr>
            <td style="width:40%;">Grado<sup>(1)</sup>: {{ $asignacion->ciclo->grado ?? 'Superior' }}</td>
            <td style="width:30%;">Código<sup>(2)</sup>: {{ $asignacion->ciclo->codigo ?? '' }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Nº de horas realizadas: {{ $asignacion->num_horas ?? '' }}</td>
            <td style="text-align:center;">
                Periodo de formación:
                1º <span class="checkbox">{{ $asignacion->numero_curso == 1 ? 'X' : '' }}</span>
                2º <span class="checkbox">{{ $asignacion->numero_curso == 2 ? 'X' : '' }}</span>
                3º <span class="checkbox">{{ $asignacion->numero_curso == 3 ? 'X' : '' }}</span>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>

    {{-- AREAS Y PUESTOS --}}
    <table>
        <tr>
            <td class="label area-grande" style="font-weight:bold; font-size:8.5pt;">
                Áreas y/o puestos de trabajo en los que se han realizado las actividades formativas.
                Valoración del desempeño de las actividades realizadas por el/la alumno/a.
            </td>
        </tr>
        <tr><td style="height:18mm;">&nbsp;</td></tr>
    </table>

    {{-- COMPETENCIAS TRANSVERSALES --}}
    <table>
        <tr><td class="label">Valoración de las competencias transversales</td></tr>
        <tr><td style="height:16mm;">&nbsp;</td></tr>
    </table>

    {{-- OBSERVACIONES --}}
    <table>
        <tr><td class="label">Observaciones del tutor de empresa u organismo equiparado</td></tr>
        <tr><td style="height:16mm;">&nbsp;</td></tr>
    </table>

    {{-- PÁGINA 2: RA --}}
    <div style="page-break-before: always; padding-top:8mm;">

        <table class="ra-table">
            <tr>
                <td colspan="4" style="font-weight:bold; background:#e8e8e8; font-size:8.5pt;">
                    Resultados de aprendizaje contenidos en el plan de formación, asociados a las actividades desarrolladas por el/la alumno/a
                </td>
                <th style="text-align:center; background:#d0d0d0;">Superado *</th>
                <th style="text-align:center; background:#d0d0d0;">No superado *</th>
            </tr>
            <tr>
                <th colspan="2" style="width:35%;">Módulo</th>
                <th colspan="2" style="width:45%;">Resultado de aprendizaje</th>
                <th style="width:10%; text-align:center;">&nbsp;</th>
                <th style="width:10%; text-align:center;">&nbsp;</th>
            </tr>
            @php
                $ras = $asignacion->resultadosAprendizaje;
            @endphp
            @forelse($ras as $ra)
            <tr>
                <td colspan="2" style="font-size:8.5pt;">{{ $ra->modulo->nombre ?? '' }}</td>
                <td colspan="2" style="font-size:8.5pt;">
                    RA{{ $ra->numero ?? '' }}: {{ $ra->descripcion ?? $ra->nombre ?? '' }}
                </td>
                <td style="text-align:center;"><span class="checkbox"></span></td>
                <td style="text-align:center;"><span class="checkbox"></span></td>
            </tr>
            @empty
            @for($i = 0; $i < 6; $i++)
            <tr>
                <td colspan="2" style="height:7mm;">&nbsp;</td>
                <td colspan="2">&nbsp;</td>
                <td style="text-align:center;"><span class="checkbox"></span></td>
                <td style="text-align:center;"><span class="checkbox"></span></td>
            </tr>
            @endfor
            @endforelse
        </table>

        {{-- NO SUPERADOS --}}
        <table style="margin-top:6px;">
            <tr><td class="label">En caso de no superar resultados de aprendizaje, exposición de motivos:</td></tr>
            <tr><td style="height:22mm;">&nbsp;</td></tr>
        </table>

        {{-- FIRMA --}}
        <table style="margin-top:14mm; border:none;">
            <tr>
                <td style="border:none; text-align:center;">
                    En {{ $centro['localidad'] ?? '…………………………' }}
                    a _____ de _________________ de {{ now()->year }}<br><br>
                    El/La tutor/a de empresa u organismo equiparado<br>
                    <em>(Firma digital preferentemente)</em><br><br><br><br>
                    _______________________________<br>
                    <small>{{ optional($asignacion->tutorEmpresa)->nombre ?? '' }}
                        {{ optional($asignacion->tutorEmpresa)->apellidos ?? '' }}</small>
                </td>
            </tr>
        </table>

        {{-- NOTAS AL PIE --}}
        <table style="margin-top:10px; border:none;">
            <tr>
                <td style="border:none; font-size:7.5pt; color:#444;">
                    (1) Indíquese básico, medio o superior, según proceda.<br>
                    (2) Indíquese el código identificativo que consta en el correspondiente Decreto de título.<br>
                    * A cumplimentar por el tutor/a de la empresa u organismo equiparado.
                </td>
            </tr>
        </table>

    </div>

</div>
</body>
</html>

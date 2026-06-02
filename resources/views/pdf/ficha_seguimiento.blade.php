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
    .checkbox { border: 1px solid #000; display: inline-block; width: 9px; height: 9px; margin: 0 3px; }
    .actividades-table th { background: #d0d0d0; text-align: center; font-size: 8pt; }
    .actividades-table td { font-size: 8.5pt; }
</style>
</head>
<body>
<div class="page">

    <h1>Anexo</h1>
    <h2>Ficha de seguimiento periódico</h2>

    {{-- CABECERA CURSO/CONVENIO --}}
    <table style="margin-bottom:10px;">
        <tr>
            <th style="width:33%; text-align:center;">Curso académico</th>
            <th style="width:34%; text-align:center;">Nº del Convenio o Acuerdo de aprendizaje</th>
            <th style="width:33%; text-align:center;">Nº del Anexo Relación de alumnos</th>
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
        <tr>
            <td colspan="2">E-mail de contacto: {{ $asignacion->alumno->email ?? '' }}</td>
        </tr>
    </table>

    {{-- DATOS EMPRESA --}}
    <table>
        <tr><td colspan="3" class="label">Datos del centro de trabajo</td></tr>
        <tr><td colspan="3">DENOMINACIÓN: {{ $asignacion->empresa->nombre ?? '' }}</td></tr>
        <tr><td colspan="3" class="label" style="font-style:italic;">Tutor/a de la empresa u organismo equiparado</td></tr>
        <tr>
            <td style="width:40%;">Apellidos: {{ optional($asignacion->tutorEmpresa)->apellidos ?? '' }}</td>
            <td style="width:30%;">Nombre: {{ optional($asignacion->tutorEmpresa)->nombre ?? '' }}</td>
            <td>Email: {{ optional($asignacion->tutorEmpresa)->email ?? '' }}</td>
        </tr>
    </table>

    {{-- PERIODO SEGUIMIENTO --}}
    <table>
        <tr><td colspan="2" class="label">Periodo de seguimiento de las actividades formativas</td></tr>
        <tr>
            <td>De (dd/mm/aa): {{ $asignacion->fecha_inicio?->format('d/m/Y') ?? '___________' }}</td>
            <td>a (dd/mm/aa): {{ $asignacion->fecha_fin?->format('d/m/Y') ?? '___________' }}</td>
        </tr>
    </table>

    {{-- TABLA RA/CE --}}
    <table class="actividades-table" style="margin-top:6px;">
        <tr>
            <td colspan="7" style="text-align:center; font-weight:bold; background:#e8e8e8; font-size:8.5pt;">
                Resultados de aprendizaje contenidos en el plan de formación, asociados a las actividades desarrolladas
            </td>
        </tr>
        <tr>
            <th style="width:28%;">Actividad formativa desarrollada</th>
            <th style="width:13%;">Código módulo profesional</th>
            <th style="width:7%;">R.A</th>
            <th style="width:10%;">No superado</th>
            <th style="width:10%;">En proceso</th>
            <th style="width:10%;">Superado</th>
            <th style="width:22%;">Observaciones</th>
        </tr>
        @php
            $ras = $asignacion->resultadosAprendizaje;
        @endphp
        @forelse($ras as $ra)
        <tr>
            <td style="height:8mm;">&nbsp;</td>
            <td style="text-align:center;">{{ $ra->modulo->codigo ?? '' }}</td>
            <td style="text-align:center;">{{ $ra->numero ?? '' }}</td>
            <td style="text-align:center;"><span class="checkbox"></span></td>
            <td style="text-align:center;"><span class="checkbox"></span></td>
            <td style="text-align:center;"><span class="checkbox"></span></td>
            <td>&nbsp;</td>
        </tr>
        @empty
        @for($i = 0; $i < 7; $i++)
        <tr>
            <td style="height:8mm;">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td style="text-align:center;"><span class="checkbox"></span></td>
            <td style="text-align:center;"><span class="checkbox"></span></td>
            <td style="text-align:center;"><span class="checkbox"></span></td>
            <td>&nbsp;</td>
        </tr>
        @endfor
        @endforelse
    </table>

    {{-- FIRMA TUTOR EMPRESA --}}
    <table style="margin-top:14mm; border:none;">
        <tr>
            <td style="border:none; text-align:center;">
                El/la tutor/a de la empresa u organismo equiparado
                <em>(Firma digital preferentemente)</em><br><br><br><br>
                Fecha: ………………………………
            </td>
        </tr>
    </table>

    {{-- PIE --}}
    <table style="margin-top:10px;">
        <tr>
            <td class="label" colspan="4">Destinatario: profesor/a tutor/a del centro docente</td>
        </tr>
    </table>

</div>
</body>
</html>

@extends('layouts.app')

@section('title', 'Asignar Rol — ' . $usuario->nombre)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.usuarios.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Asignar Rol</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $usuario->nombre }}</p>
        </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-blue-800 dark:text-blue-200">
                Los datos del usuario (nombre, email, contraseña y estado activo) se gestionan desde la <strong>aplicación de administración LDAP</strong>. Aquí solo se asigna el rol dentro del sistema FFE.
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide mb-4">Datos del usuario (solo lectura)</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-xs text-slate-400 dark:text-slate-500 mb-1">Nombre</dt>
                <dd class="font-medium text-slate-800 dark:text-white">{{ $usuario->nombre }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 dark:text-slate-500 mb-1">Usuario LDAP</dt>
                <dd class="font-mono text-sm text-slate-700 dark:text-slate-300">{{ $usuario->username }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-slate-400 dark:text-slate-500 mb-1">Email</dt>
                <dd class="text-slate-700 dark:text-slate-300">{{ $usuario->email }}</dd>
            </div>
        </dl>
    </div>

    <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6">

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                    Rol en el sistema FFE <span class="text-red-500">*</span>
                </label>
                @if($usuario->id === auth()->id() && $usuario->esAdmin())
                <div class="mb-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-700 dark:text-amber-300">
                    No puedes cambiar tu propio rol de administrador.
                </div>
                @endif
                @php $esYoAdmin = $usuario->id === auth()->id() && $usuario->esAdmin(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="flex items-start p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-primary-300 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-colors {{ $esYoAdmin ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="radio" name="rol" value="profesor" {{ old('rol', $usuario->rol) === 'profesor' ? 'checked' : '' }}
                               class="mt-0.5 text-primary-600 focus:ring-primary-500" onchange="toggleCicloSelect()" {{ $esYoAdmin ? 'disabled' : '' }}>
                        <div class="ml-3">
                            <span class="block font-medium text-slate-800 dark:text-white">Profesor</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Gestiona sus propias empresas</span>
                        </div>
                    </label>
                    <label class="flex items-start p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-amber-300 hover:bg-amber-50/50 dark:hover:bg-amber-900/20 transition-colors {{ $esYoAdmin ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="radio" name="rol" value="responsable_ciclo" {{ old('rol', $usuario->rol) === 'responsable_ciclo' ? 'checked' : '' }}
                               class="mt-0.5 text-amber-600 focus:ring-amber-500" onchange="toggleCicloSelect()" {{ $esYoAdmin ? 'disabled' : '' }}>
                        <div class="ml-3">
                            <span class="block font-medium text-slate-800 dark:text-white">Responsable de Ciclo</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Gestiona empresas de sus ciclos</span>
                        </div>
                    </label>
                    <label class="flex items-start p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-teal-300 hover:bg-teal-50/50 dark:hover:bg-teal-900/20 transition-colors {{ $esYoAdmin ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="radio" name="rol" value="responsable_ffe" {{ old('rol', $usuario->rol) === 'responsable_ffe' ? 'checked' : '' }}
                               class="mt-0.5 text-teal-600 focus:ring-teal-500" onchange="toggleCicloSelect()" {{ $esYoAdmin ? 'disabled' : '' }}>
                        <div class="ml-3">
                            <span class="block font-medium text-slate-800 dark:text-white">Responsable FFE</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Accede a todas las empresas</span>
                        </div>
                    </label>
                    <label class="flex items-start p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50/50 dark:hover:bg-red-900/20 transition-colors">
                        <input type="radio" name="rol" value="admin" {{ old('rol', $usuario->rol) === 'admin' ? 'checked' : '' }}
                               class="mt-0.5 text-red-600 focus:ring-red-500" onchange="toggleCicloSelect()">
                        <div class="ml-3">
                            <span class="block font-medium text-slate-800 dark:text-white">Administrador</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Acceso total al sistema</span>
                        </div>
                    </label>
                </div>
                @error('rol')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div id="ciclo-container" class="{{ old('rol', $usuario->rol) === 'responsable_ciclo' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Ciclos formativos asignados <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Selecciona uno o varios ciclos que gestionará este responsable</p>
                @php $ciclosUsuario = $usuario->ciclos->pluck('id')->toArray(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($ciclos as $ciclo)
                    <label class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-primary-300 hover:bg-primary-50/30 dark:hover:bg-primary-900/20 transition-colors">
                        <input type="checkbox" name="ciclos[]" value="{{ $ciclo->id }}"
                               {{ in_array($ciclo->id, old('ciclos', $ciclosUsuario)) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold mr-2
                                @if($ciclo->nivel === 'basica') bg-orange-100 text-orange-700
                                @elseif($ciclo->nivel === 'media') bg-blue-100 text-blue-700
                                @else bg-purple-100 text-purple-700 @endif">
                                {{ $ciclo->codigo }}
                            </span>
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $ciclo->nombre }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('ciclos')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('admin.usuarios.index') }}" class="px-6 py-2.5 text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 shadow-lg shadow-primary-500/20">
                Guardar Rol
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleCicloSelect() {
    const rol = document.querySelector('input[name="rol"]:checked')?.value;
    document.getElementById('ciclo-container').classList.toggle('hidden', rol !== 'responsable_ciclo');
}
document.addEventListener('DOMContentLoaded', toggleCicloSelect);
</script>
@endpush
@endsection

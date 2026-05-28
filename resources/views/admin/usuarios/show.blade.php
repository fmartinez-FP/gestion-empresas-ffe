@extends('layouts.app')

@section('title', $usuario->nombre)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Cabecera -->
    <div class="flex items-start gap-4">
        <a href="{{ route('admin.usuarios.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg mt-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 rounded-full flex items-center justify-center text-white font-bold text-2xl
                    @if($usuario->rol === 'admin') bg-gradient-to-br from-red-500 to-red-600
                    @elseif($usuario->rol === 'responsable_ciclo') bg-gradient-to-br from-amber-500 to-amber-600
                    @else bg-gradient-to-br from-primary-500 to-primary-600 @endif">
                    {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">{{ $usuario->nombre }}</h1>
                    <p class="text-slate-500">{{ $usuario->email }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.usuarios.edit', $usuario) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Datos del usuario -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Información del Usuario</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-slate-500">Nombre</dt>
                        <dd class="font-medium text-slate-800">{{ $usuario->nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Email</dt>
                        <dd class="font-medium text-slate-800">{{ $usuario->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Rol</dt>
                        <dd>
                            @php
                                $rolColor = match($usuario->rol) {
                                    'admin' => 'bg-red-100 text-red-700',
                                    'responsable_ciclo' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-blue-100 text-blue-700',
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold {{ $rolColor }}">
                                {{ $usuario->nombre_rol }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Estado</dt>
                        <dd>
                            @if($usuario->activo)
                            <span class="inline-flex items-center gap-1 text-green-600">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-slate-400">
                                <span class="w-2 h-2 bg-slate-300 rounded-full"></span>
                                Inactivo
                            </span>
                            @endif
                        </dd>
                    </div>
                    @if($usuario->ciclo)
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-slate-500">Ciclo asignado</dt>
                        <dd class="font-medium text-slate-800">{{ $usuario->ciclo->codigo }} - {{ $usuario->ciclo->nombre }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Empresas creadas -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-800">Empresas Creadas</h2>
                    <span class="text-sm text-slate-500">{{ $usuario->empresasCreadas->count() }} empresas</span>
                </div>
                @if($usuario->empresasCreadas->count() > 0)
                <ul class="divide-y divide-slate-100">
                    @foreach($usuario->empresasCreadas->take(10) as $empresa)
                    <li class="py-3">
                        <a href="{{ route('empresas.show', $empresa) }}" class="flex items-center justify-between hover:text-primary-600">
                            <span class="font-medium">{{ $empresa->nombre }}</span>
                            <span class="text-sm text-slate-500">{{ $empresa->cif }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @if($usuario->empresasCreadas->count() > 10)
                <p class="mt-3 text-sm text-slate-500">Y {{ $usuario->empresasCreadas->count() - 10 }} más...</p>
                @endif
                @else
                <p class="text-slate-400 italic">No ha creado empresas</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Estadísticas -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Estadísticas</h2>
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Empresas creadas</dt>
                        <dd class="font-semibold text-slate-800">{{ $usuario->empresasCreadas->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Colocaciones registradas</dt>
                        <dd class="font-semibold text-slate-800">{{ $usuario->colocacionesRegistradas->count() }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Metadatos -->
            <div class="bg-slate-50 rounded-xl p-4">
                <dl class="text-sm text-slate-500 space-y-2">
                    <div class="flex justify-between">
                        <dt>Creado</dt>
                        <dd>{{ $usuario->created_at->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Actualizado</dt>
                        <dd>{{ $usuario->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Editar Contacto')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Cabecera -->
    <div class="flex items-center gap-4">
        <a href="{{ route('empresas.show', $empresa) }}" 
           class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Editar Contacto</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $empresa->nombre }}</p>
        </div>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('contactos.update', [$empresa, $contacto]) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Datos del Contacto
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tipo de contacto -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Tipo de contacto <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo" id="tipo" required
                            class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="llamada" {{ old('tipo', $contacto->tipo) == 'llamada' ? 'selected' : '' }}>📞 Llamada telefónica</option>
                        <option value="email" {{ old('tipo', $contacto->tipo) == 'email' ? 'selected' : '' }}>📧 Email</option>
                        <option value="visita" {{ old('tipo', $contacto->tipo) == 'visita' ? 'selected' : '' }}>📍 Visita presencial</option>
                        <option value="reunion_online" {{ old('tipo', $contacto->tipo) == 'reunion_online' ? 'selected' : '' }}>💻 Reunión online</option>
                        <option value="otro" {{ old('tipo', $contacto->tipo) == 'otro' ? 'selected' : '' }}>📝 Otro</option>
                    </select>
                </div>

                <!-- Resultado -->
                <div>
                    <label for="resultado" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Resultado <span class="text-red-500">*</span>
                    </label>
                    <select name="resultado" id="resultado" required
                            class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="exitoso" {{ old('resultado', $contacto->resultado) == 'exitoso' ? 'selected' : '' }}>✅ Exitoso</option>
                        <option value="sin_respuesta" {{ old('resultado', $contacto->resultado) == 'sin_respuesta' ? 'selected' : '' }}>❌ Sin respuesta</option>
                        <option value="pendiente" {{ old('resultado', $contacto->resultado) == 'pendiente' ? 'selected' : '' }}>⏳ Pendiente de respuesta</option>
                        <option value="cita_programada" {{ old('resultado', $contacto->resultado) == 'cita_programada' ? 'selected' : '' }}>📅 Cita programada</option>
                    </select>
                </div>

                <!-- Fecha de contacto -->
                <div>
                    <label for="fecha_contacto" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Fecha del contacto <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="fecha_contacto" id="fecha_contacto" required
                           value="{{ old('fecha_contacto', $contacto->fecha_contacto->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <!-- Persona de contacto -->
                <div>
                    <label for="persona_contacto" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Persona contactada
                    </label>
                    <input type="text" name="persona_contacto" id="persona_contacto"
                           value="{{ old('persona_contacto', $contacto->persona_contacto) }}"
                           placeholder="Nombre de la persona"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <!-- Fecha de seguimiento -->
                <div>
                    <label for="fecha_seguimiento" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Fecha de seguimiento
                    </label>
                    <input type="datetime-local" name="fecha_seguimiento" id="fecha_seguimiento"
                           value="{{ old('fecha_seguimiento', $contacto->fecha_seguimiento?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <!-- Archivo adjunto -->
                <div>
                    <label for="archivo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Archivo adjunto
                    </label>
                    @if($contacto->archivo_adjunto)
                    <div class="mb-2 p-3 bg-slate-50 dark:bg-slate-700 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <a href="{{ route('contactos.archivo', [$empresa, $contacto]) }}" class="text-sm text-primary-600 hover:underline">
                                {{ $contacto->archivo_nombre }}
                            </a>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-red-600 cursor-pointer">
                            <input type="checkbox" name="eliminar_archivo" value="1" class="rounded border-slate-300">
                            Eliminar
                        </label>
                    </div>
                    @endif
                    <input type="file" name="archivo" id="archivo"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-900/30 dark:file:text-primary-400">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">PDF, Word, Excel o imagen. Máx. 5MB.</p>
                </div>

                <!-- Notas -->
                <div class="md:col-span-2">
                    <label for="notas" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Notas / Observaciones
                    </label>
                    <textarea name="notas" id="notas" rows="4"
                              placeholder="Detalle de la conversación, acuerdos, temas tratados..."
                              class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('notas', $contacto->notas) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <a href="{{ route('empresas.show', $empresa) }}" 
               class="px-6 py-2.5 text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-center">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection

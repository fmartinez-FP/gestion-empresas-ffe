@props([
    'label' => null,
    'name',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'hint' => null,
])

<div>
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-2">
        {{ $label }}
        @if($required)<span class="text-red-500">*</span>@endif
    </label>
    @endif
    
    <input 
        type="{{ $type }}" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        value="{{ old($name, $attributes->get('value', '')) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent' . ($errors->has($name) ? ' border-red-300' : '')]) }}
    >
    
    @if($hint)
    <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    
    @error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

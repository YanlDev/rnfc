<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ config('app.name') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
RNFC — Plataforma Web · Sistema automatizado de gestión

Este correo fue generado automáticamente. Por favor no respondas a este mensaje.

&copy; {{ date('Y') }} RNFC. @lang('Todos los derechos reservados.')
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>

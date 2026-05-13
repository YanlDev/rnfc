<x-mail::message>
# Nuevo mensaje desde la web

**Nombre:** {{ $nombre }}
**Correo:** {{ $correo }}
@if ($telefono)
**Teléfono:** {{ $telefono }}
@endif
**Asunto:** {{ $asunto }}

---

{{ $mensaje }}

---

Puedes responder directamente a este correo para contactar al remitente.

**RNFC Consultor de Obras**
</x-mail::message>

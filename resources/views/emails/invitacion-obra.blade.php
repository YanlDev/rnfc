<x-mail::message>
# Invitación a colaborar en una obra

Hola,

@if ($invitador)
**{{ $invitador->name }}** te ha invitado
@else
Has sido invitado(a)
@endif
a colaborar como **{{ $rol }}** en la obra:

**{{ $obra->nombre }}** ({{ $obra->codigo }})
@if ($obra->entidad_contratante)
{{ $obra->entidad_contratante }}
@endif

<x-mail::button :url="$urlAceptar">
Aceptar invitación
</x-mail::button>

Si ya tienes una cuenta en la plataforma, podrás aceptar con un clic. Si no, te
guiaremos para crearla con este mismo correo y serás vinculado(a) automáticamente
a la obra.

Este enlace expira el **{{ $invitacion->expira_at->format('d/m/Y H:i') }}**.

Gracias,<br>
**RNFC Consultor de Obras**
</x-mail::message>

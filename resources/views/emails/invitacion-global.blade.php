<x-mail::message>
# Invitación a colaborar en RNFC

@if ($invitador)
**{{ $invitador->name }}** te ha invitado
@else
Has sido invitado(a)
@endif
a colaborar en la plataforma **RNFC Consultor de Obras** con el rol de:

**{{ $rol }}**

<x-mail::button :url="$urlAceptar">
Aceptar invitación
</x-mail::button>

Si ya tienes una cuenta en la plataforma, podrás aceptar con un clic. Si no, te
guiaremos para crearla con este mismo correo y se te asignará el rol correspondiente.

Este enlace expira el **{{ $invitacion->expira_at->format('d/m/Y H:i') }}**.

Gracias,<br>
**RNFC Consultor de Obras**
</x-mail::message>

<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
<div class="footer-isos">
<img src="{{ rtrim(config('app.url'), '/') }}/brand/isos.png" alt="ISO 9001 · ISO 14001 · ISO 37001" style="height: 48px; max-height: 48px; width: auto;">
</div>

<p style="color: #a1a1aa; font-size: 11px; margin: 6px 0 4px;">
RNFC &mdash; Plataforma Web &middot; Sistema automatizado de gesti&oacute;n
</p>

<p style="color: #a1a1aa; font-size: 11px; margin: 0 0 12px;">
Este correo fue generado autom&aacute;ticamente. Por favor no respondas a este mensaje.
</p>

{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>

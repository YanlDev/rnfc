<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
<div class="footer-isos">
<img src="{{ rtrim(config('app.url'), '/') }}/brand/isos.png" alt="ISO 9001 &middot; ISO 14001 &middot; ISO 37001">
</div>

<p class="footer-auto">RNFC &mdash; Plataforma Web &middot; Sistema automatizado de gesti&oacute;n</p>
<p class="footer-auto-note">Este correo fue generado autom&aacute;ticamente. Por favor no respondas a este mensaje.</p>

{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>

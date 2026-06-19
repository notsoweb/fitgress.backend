<x-mail::message>
# Correo de prueba

Este es un correo de prueba enviado desde **{{ $appName }}** para verificar que la configuración de correo funciona correctamente.

**Mailer:** {{ $mailer }}  
**Fecha:** {{ $sentAt }}  
**URL:** {{ $appUrl }}

Si recibiste este mensaje, el envío de correos está operativo.

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>

# REVERB

Laravel Reverb incorpora una comunicación WebSocket en tiempo real, ultrarrápida y escalable, directamente en tu aplicación Laravel, y ofrece una integración perfecta con el conjunto de eventos ya existente de Laravel.

## Configuración
Ejemplo de configuración:
```bash
[program:argos-reverb]
process_name=%(program_name)s
directory=/var/www/mdev/argos.backend
command=php artisan reverb:start --port=8080
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/argos/reverb.log
stopwaitsecs=10
```

Se debe de editar:
- directory: Colocar el directorio real del proyecto.
- stdout_logfile: La carpeta argos no existe, se debe crear para poder guardar el log.
- command: Puedes agregar más parametros, pero se debe de cuidar el port, ya que puede haber otras aplicaciones corriendo con ese puerto, en ese caso solo se debe de cambiar el puerto y configurarlo en las variables de entorno de laravel y nginx (si se usa el proxy inverso).

Por estándar se debe guardar el archivo con el nombre colocado en el program con la extención `conf` en el path `/etc/supervisor/config.d`.
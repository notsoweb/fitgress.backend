# QUEUE DEFAULT

Permite configurar la cola de QUEUE, para poder procesar jobs.

## Configuración
Ejemplo de configuración por default:
```bash
[program:argos-queue]
process_name=%(program_name)s
directory=/var/www/mdev/argos.backend
command=php artisan queue:work 
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/argos/queue.log
stopwaitsecs=10
```

Se debe de editar:
- directory: Colocar el directorio real del proyecto.
- stdout_logfile: La carpeta argos no existe, se debe crear para poder guardar el log.
- command: Puedes agregar más parametros como el timeout, nombre del queue por si se requiere uno personalizado.

Por estándar se debe guardar el archivo con el nombre colocado en el program con la extención `conf` en el path `/etc/supervisor/config.d`.

## Listado de queues
En esta aplicación usamos:
- default: Procesar cola de notificaciones.
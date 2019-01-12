# Zeus Websockets
Servidor de websockets para Laravel

## Instalación
Mediante composer:
```
composer require jaguadoromero/zeus-websockets
```

## Configuración
La configuración se realiza en el `.env` del proyecto.

>
> **`WEBSOCKETS_PORT=6001`**  
> El puerto sobre el que correrá el servidor de websockets. Por defecto es **6001**.

> **`WEBSOCKETS_SSL=false`**  
> Indica si servidor correrá sobre SSL o no. Por defecto es **false**. Si es **true** se deben especificar las variables **`WEBSOCKETS_CERT`** y **`WEBSOCKETS_PK`**.

> **`WEBSOCKETS_CERT=`**  
> La ruta absoluta al archivo de certificado.

> **`WEBSOCKETS_PK=`**  
> La ruta absoluta al archivo de clave privada.

## Ejecutar servidor
El servidor se pone en marcha mediante un comando de Artisan.

**Manualmente**
```
php artisan websockets:server
```

**Mediante supervisor**  
Para dejar corriendo el servidor mediante *`Supervisor`* , añadir en el archivo de configuración de Supervisor:

```
[program:websockets-server]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan websockets:server
autostart=true
autorestart=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/websockets.log
```
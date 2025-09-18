# Utiliza una imagen base de PHP con Apache.
# Esta imagen ya viene configurada para un buen rendimiento.
FROM php:8.2-apache

# Instala el cliente de PostgreSQL y otras herramientas necesarias.
# Utiliza un solo comando `apt-get install` para reducir el tamaño de la imagen.
RUN apt-get update && \
    apt-get install -y \
        libpq-dev \
        postgresql-client \
    --no-install-recommends && \
    rm -rf /var/lib/apt/lists/*

# Instala la extensión de PHP para PostgreSQL (pdo_pgsql y pgsql).
# El `docker-php-ext-install` es la forma oficial y recomendada.
RUN docker-php-ext-install pdo_pgsql pgsql

# Copia los archivos de tu aplicación PHP al directorio de Apache.
# Cambia 'src/' por la ruta de tu aplicación.
COPY test_kleppi/ /var/www/html/

# Configura las directivas de Apache para mejorar el rendimiento.
# Deshabilita la resolución de DNS en los logs para evitar latencia.
# Esto es opcional, pero ayuda a la velocidad.
RUN a2enmod rewrite && \
    echo "HostnameLookups Off" >> /etc/apache2/apache2.conf

# Expone el puerto por defecto de Apache.
EXPOSE 80

# El comando por defecto de la imagen base de php-apache
# ya inicia el servidor Apache. No se requiere un CMD o ENTRYPOINT adicional.

# Para construir la imagen, usa:
# docker build -t mi-app-php-pg .

# Para ejecutar un contenedor, usa:
# docker run -d --name mi-contenedor -p 8080:80 mi-app-php-pg
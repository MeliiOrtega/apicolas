# Usar una imagen base de PHP con Apache
FROM php:8.1-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar los archivos del proyecto
COPY . .

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite

# Instalar dependencias de Laravel
RUN composer install --no-interaction --optimize-autoloader

# Configurar el archivo de entorno
COPY .env.example .env
RUN php artisan key:generate

# Comando por defecto
CMD ["apache2-foreground"]

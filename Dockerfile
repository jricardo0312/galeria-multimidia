FROM php:8.2-apache

# Configuração do ambiente não interativo durante build
ENV DEBIAN_FRONTEND=noninteractive

# Instalação de dependências do sistema e utilitários
RUN apt-get update && apt-get install -y \
    ffmpeg \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configuração e instalação de extensões PHP essenciais (Laravel, CI4, PDO, GD, Intl)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        mysqli \
        zip \
        intl \
        bcmath \
        opcache

# Ativação do módulo rewrite do Apache (Essencial para Laravel e CI4)
RUN a2enmod rewrite

# Instalação do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# # Sobrescrita de configurações do PHP (Aumentando limites de upload e tempo de execução)
# RUN echo "upload_max_filesize = 1000M" > /usr/local/etc/php/conf.d/uploads.ini \
#     && echo "post_max_size = 1020M" >> /usr/local/etc/php/conf.d/uploads.ini \
#     && echo "memory_limit = 1024M" >> /usr/local/etc/php/conf.d/uploads.ini \
#     && echo "max_execution_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini \
#     && echo "max_input_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini

# Sobrescrita de configurações do PHP
RUN echo "upload_max_filesize = 1000M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 1020M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 1024M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_input_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_file_uploads = 100" >> /usr/local/etc/php/conf.d/uploads.ini

# Remoção de limite do payload do Apache
RUN echo "LimitRequestBody 0" >> /etc/apache2/apache2.conf

# Ajuste do DocumentRoot do Apache para apontar para a raiz do projeto atual
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

EXPOSE 80

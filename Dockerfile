# 1. Estágio de construção: Aqui instalamos as dependências do PHP e Composer
FROM php:8.3-fpm-alpine as build

# Instala dependências do sistema e extensões do PHP necessárias para o Laravel
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        opcache

# Instala o Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www

# Copia apenas os arquivos de definição de dependências primeiro (para aproveitar o cache do Docker)
COPY composer.json composer.lock ./

# Instala as dependências do PHP (sem as de desenvolvimento)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copia o resto do código da aplicação
COPY . .

# 2. Estágio final: Cria a imagem final de produção
FROM php:8.3-fpm-alpine

# Instala as mesmas dependências de sistema e extensões do PHP que no estágio de construção
RUN apk add --no-cache \
        nginx \
        supervisor \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        opcache

# Copia as configurações e a aplicação do estágio de construção
COPY --from=build /var/www /var/www
COPY --from=build /usr/bin/composer /usr/bin/composer

# Copia os arquivos de configuração do Nginx e do Supervisor
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Define as permissões corretas para os diretórios que o Laravel precisa escrever
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Cria o link simbólico para o storage
RUN php artisan storage:link

# Expõe a porta 80
EXPOSE 80

# Inicia o Supervisor, que por sua vez inicia o PHP-FPM e o Nginx
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

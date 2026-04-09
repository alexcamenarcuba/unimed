FROM php:8.4-fpm

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Node.js e npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${APP_GID} appuser \
    && useradd -u ${APP_UID} -g appuser -m appuser

WORKDIR /var/www

# Copiar todo o projeto
COPY web/ ./

# Instalar dependências PHP
RUN composer install --no-interaction

# Instalar dependências Node
RUN npm install

# Garante ownership correto quando Docker inicializa volumes a partir da imagem
RUN chown -R appuser:appuser /var/www/vendor /var/www/node_modules

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV HOME=/tmp
ENV NPM_CONFIG_CACHE=/tmp/.npm
ENV COMPOSER_HOME=/tmp/.composer

USER appuser

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
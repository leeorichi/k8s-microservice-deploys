FROM php:8.1-fpm

ENV PHP_EXTRA_CONFIGURE_ARGS=--enable-fpm --with-fpm-user=www-data --with-fpm-group=www-data

# Install dependencies
RUN apt-get update && apt-get install -y \
    cron \
    dos2unix \
    apt-utils \
    build-essential \
    vim \
    locales \
    zip \
    unzip \
    curl \
    supervisor \
    libzip-dev \
	libpng-dev \
	telnet \
    logrotate

# Install Redis cache
RUN pecl install redis-5.3.7 \
	&& docker-php-ext-enable redis

# Install kafka client
RUN apt-get install -y librdkafka-dev && \
    pecl install rdkafka && \
    docker-php-ext-enable rdkafka

# Make folder for Suspervisor.
RUN mkdir -p /var/log/supervisor
RUN chmod 777 -R /var/log/supervisor/
COPY ./server/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql bcmath sockets zip gd
RUN docker-php-ext-enable pdo_mysql bcmath sockets zip gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN usermod -u 1000 www-data

# Copy existing application directory contents
#COPY . /var/www

# Copy existing application directory permissions
#COPY --chown=www-data:www-data . /var/www

# Change current user to root to using supervisor

# Setup Cronjob
COPY ./server/cron/k8s /etc/cron.d/
RUN chmod 644 /etc/cron.d/k8s
RUN crontab /etc/cron.d/k8s

COPY ./server/cron/logrotate /etc/cron.daily/
RUN chmod 644 /etc/cron.daily/logrotate

# Setup logrotate
COPY ./server/logrotate/k8s /etc/logrotate.d/
RUN chmod 644 /etc/logrotate.d/k8s
RUN dos2unix -b /etc/logrotate.d/k8s
RUN ls -la /etc/logrotate.d/

# Expose port 9000
EXPOSE 9000

# Run Supervisord( start php-fpm server and php kafka )
CMD ["/usr/bin/supervisord"]

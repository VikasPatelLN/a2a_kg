FROM useast.jfrog.lexisnexisrisk.com/mbs-docker-dev-local/ui-php8-apache-laravel:latest as base

FROM base as deployment
######################################
## MBS UI Specific Changes
######################################
WORKDIR /var/www/html

# Copy the code into the container
COPY --chown=www-data:www-data . /var/www/html 
# Creating temp storage
RUN mkdir /var/www/html/storage/app/livewire-tmp/ && touch /var/www/html/storage/logs/laravel.log && chown -R www-data:www-data /var/www/html

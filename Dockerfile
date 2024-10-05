FROM php:8.3-alpine

WORKDIR /app

RUN apk update
RUN apk add git composer libxml2-dev php-ctype php-xml php-dom php-xmlwriter php-tokenizer

RUN git clone https://github.com/Mediashare/marathon.git /app

RUN composer install --no-scripts --no-autoloader
RUN composer dump-autoload --optimize

RUN mv docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]

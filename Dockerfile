FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite headers

RUN apt-get update && apt-get install -y nano \
  && echo "Header set Access-Control-Allow-Origin \"*\"" >> /etc/apache2/apache2.conf

# 폴더 및 파일 복사
COPY ./public/ /var/www/html/
COPY ./public/uploads/reviews/ /var/www/html/uploads/reviews/
COPY ./includes/ /var/www/html/includes/
COPY ./includes/ /var/www/includes/
COPY ./vendor/ /var/www/vendor/
COPY .env /var/www/.env

# 퍼미션
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

EXPOSE 80

# PHP + Apache 공식 이미지 사용
FROM php:8.2-apache

# 필요한 PHP 확장 설치
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# CORS 허용 설정
RUN apt-get update && apt-get install -y nano \
  && echo "Header set Access-Control-Allow-Origin \"*\"" >> /etc/apache2/apache2.conf

# public 폴더 복사
COPY ./public/ /var/www/html/

# 리뷰 이미지 복사
COPY ./public/uploads/reviews/ /var/www/html/uploads/reviews/

# includes 복사
COPY ./includes/ /var/www/html/includes/
COPY ./includes/ /var/www/includes/

# composer.json, composer.lock, .env 복사
COPY composer.json composer.lock .env /var/www/

# 작업 디렉토리 설정
WORKDIR /var/www

# Composer 설치
RUN composer install --no-dev --optimize-autoloader

# 퍼미션 설정
RUN chown -R www-data:www-data /var/www \
  && chmod -R 755 /var/www

# 기본 포트
EXPOSE 80

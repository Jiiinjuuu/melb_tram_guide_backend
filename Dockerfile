# PHP + Apache 공식 이미지 사용
FROM php:8.2-apache

# 필요한 PHP 확장 설치 (PDO, mysqli 등)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# CORS 허용을 위한 Apache 설정 추가
RUN apt-get update && apt-get install -y nano \
  && echo "Header set Access-Control-Allow-Origin \"*\"" >> /etc/apache2/apache2.conf

# mod_rewrite 활성화 (라우팅 위해 필요할 수 있음)
RUN a2enmod rewrite

# 프로젝트 복사
COPY ./public/ /var/www/html/

# 퍼미션 설정
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

# 기본 포트 80 사용
EXPOSE 80

# استخدم صورة PHP مع Apache
FROM php:8.2-apache

# تثبيت مكتبات GD و dependencies
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        unzip \
        curl \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install gd \
        && apt-get clean && rm -rf /var/lib/apt/lists/*

# نسخ ملفات المشروع إلى مجلد الويب
COPY . /var/www/html/

# فعل mod_rewrite لو يحتاج البوت ملفات Webhook
RUN a2enmod rewrite

# تعيين صلاحيات www-data
RUN chown -R www-data:www-data /var/www/html

# المنفذ الافتراضي
EXPOSE 80

# تعيين نقطة دخول لتشغيل بوت Polling إذا أردت
# يمكن تشغيله من خلال: docker exec -it <container_name> php /var/www/html/bot.php

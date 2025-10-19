# Use the official PHP Apache image
FROM php:8.2-apache

# Copy project files to Apache web root
COPY . /var/www/html/

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Expose port 80 for local and dynamic port on Railway
EXPOSE 80

# Bind Apache to the dynamic Railway PORT environment variable
CMD ["bash", "-c", "sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf && apache2-foreground"]

FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    python3 \
    python3-venv \
    libgl1 \
    libglib2.0-0 \
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN python3 -m venv /opt/venv

RUN /opt/venv/bin/pip install --no-cache-dir -r /var/www/html/requirements.txt

ENV PATH="/opt/venv/bin:$PATH"

EXPOSE 80

CMD ["apache2-foreground"]

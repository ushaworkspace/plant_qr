FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    python3 \
    python3-venv \
    libgl1 \
    libglib2.0-0 \
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

COPY php.ini /usr/local/etc/php/conf.d/uploads.ini

RUN python3 -m venv /opt/venv

RUN /opt/venv/bin/pip install --no-cache-dir -r /var/www/html/requirements.txt

ENV PATH="/opt/venv/bin:$PATH"

WORKDIR /var/www/html

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
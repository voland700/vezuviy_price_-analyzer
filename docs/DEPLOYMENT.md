# Развёртывание (LEMP на Ubuntu)

Пошаговое руководство по развёртыванию Analyzer на стеке
**Linux + Nginx + MySQL + PHP-FPM** с подключением Python-парсера.

> В примерах используется условный домен `analyzer.example.com` и путь
> `/var/www/analyzer`. В рабочем окружении проекта это `/var/www/dm.pechnik.su`.

---

## 1. Системные требования

| Компонент | Версия |
|---|---|
| ОС | Ubuntu 22.04 / 24.04 (или аналог) |
| PHP | 8.3+ |
| Composer | 2.7+ |
| Node.js | 18+ (тестировалось на 22) + npm |
| MySQL | 8.0+ / MariaDB 10.4+ |
| Python | 3.10+ (тестировалось на 3.12) |
| Nginx | 1.24+ |

PHP-расширения: `mbstring`, `xml`, `pdo`, `pdo_mysql`, `bcmath`, `curl`,
`zip`, `gd` (или `imagick`).

---

## 2. Установка системных пакетов

```bash
sudo apt-get update

# PHP и расширения
sudo apt-get install -y php8.3-fpm php8.3-mbstring php8.3-xml php8.3-mysql \
    php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd unzip

# Nginx, MySQL, Python-зависимости
sudo apt-get install -y nginx mysql-server python3-pymysql
```

Дополнительно:

```bash
# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js (через NodeSource)
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt-get install -y nodejs
```

---

## 3. База данных

```bash
sudo mysql
```

```sql
CREATE DATABASE analyzer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'analyzer'@'localhost' IDENTIFIED BY 'СЛОЖНЫЙ_ПАРОЛЬ';
GRANT ALL PRIVILEGES ON analyzer.* TO 'analyzer'@'localhost';
FLUSH PRIVILEGES;
```

---

## 4. Развёртывание приложения

```bash
# Разместите код проекта
sudo mkdir -p /var/www/analyzer
sudo chown -R $USER:www-data /var/www/analyzer

# Если из git:
git clone <repo-url> /var/www/analyzer
cd /var/www/analyzer

# Зависимости
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Конфигурация окружения

```bash
cp .env.example .env
php artisan key:generate
```

Отредактируйте `.env`:

```dotenv
APP_NAME=Analyzer
APP_ENV=production
APP_DEBUG=false
APP_URL=https://analyzer.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=analyzer
DB_USERNAME=analyzer
DB_PASSWORD=СЛОЖНЫЙ_ПАРОЛЬ

# Сессии/кэш/очереди — через БД (таблицы создаются миграциями)
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Миграции и права

```bash
php artisan migrate --force

# Права на каталоги, требующие записи
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

### Оптимизация (продакшн)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
# php artisan event:cache   # при необходимости
```

> После каждого обновления кода повторяйте `config/route/view:cache` и
> `npm run build`.

---

## 5. Настройка Nginx

`/etc/nginx/sites-available/analyzer`:

```nginx
server {
    listen 80;
    server_name analyzer.example.com;
    root /var/www/analyzer/public;
    index index.php index.html;

    # Редирект HTTP -> HTTPS (после настройки сертификата)
    # return 301 https://$host$request_uri;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Запрет доступа к скрытым файлам и sensitive путям
    location ~ /\.(?!well-known).* { deny all; }

    location ~ ^/(storage|app)/.*$ { deny all; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ !\.(php|html|css|js|svg|png|jpg|jpeg|gif|ico|woff2?)$ {
        try_files $uri /index.php?$query_string;
    }
}
```

Активация:

```bash
sudo ln -s /etc/nginx/sites-available/analyzer /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### HTTPS (Let's Encrypt)

```bash
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d analyzer.example.com
```

---

## 6. Права доступа (владелец файлов)

Рекомендуемая схема: владелец-разработчик + группа `www-data`:

```bash
sudo chown -R $USER:www-data /var/www/analyzer
sudo find /var/www/analyzer -type d -exec chmod 755 {} \;
sudo find /var/www/analyzer -type f -exec chmod 644 {} \;

# Каталоги с записью — группе даём запись
sudo chmod -R ug+rwx /var/www/analyzer/storage /var/www/analyzer/bootstrap/cache
```

---

## 7. Python-парсер и автоматизация

Скрипт использует те же учётные данные БД из `.env`. Убедитесь, что
пользователь, от которого запускается парсер, имеет доступ на чтение `.env`.

Разовый запуск:

```bash
python3 /var/www/analyzer/scripts/analyzer.py
```

### Расписание (cron)

Каждый час с записью лога:

```cron
0 * * * * /usr/bin/python3 /var/www/analyzer/scripts/analyzer.py >> /var/log/analyzer.log 2>&1
```

Каталог лога должен быть доступен на запись:

```bash
sudo touch /var/log/analyzer.log
sudo chown $(whoami):$(whoami) /var/log/analyzer.log
```

> Альтернатива — запуск через `systemd timer` или `supervisor` для более
> тонкого контроля. Подробности по работе парсера — в
> [`../scripts/README.md`](../scripts/README.md).

### Laravel scheduler (опционально)

Если потребуется дёргать парсер через Laravel — добавьте Artisan-команду и
запись в `app/Console/Kernel.php`, а в cron одну строку для планировщика:

```cron
* * * * * cd /var/www/analyzer && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. Создание первого пользователя

После развёртывания откройте в браузере:

```
https://analyzer.example.com/register
```

Зарегистрируйте учётную запись администратора. После этого (для продакшн)
маршрут `/register` можно отключить, если регистрация не нужна:

```php
// routes/auth.php — закомментируйте Route::get('/register', ...)
```

---

## 9. Чек-лист обновления (деплой новой версии)

```bash
cd /var/www/analyzer
git pull
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan view:clear
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

При переводе приложения в режим обслуживания:

```bash
php artisan down        # включить заглушку
# ... обновление ...
php artisan up          # вернуть в работу
```

---

## 10. Диагностика

| Симптом | Что проверить |
|---|---|
| Белый экран / 500 | `APP_DEBUG=false` → смотрите `storage/logs/laravel.log` |
| `500` на экспорте XLSX | установлено ли PHP-расширение `zip` |
| Пустые таблицы | запущен ли `scripts/analyzer.py`; есть ли данные в БД |
| `Permission denied` в `storage/` | права `www-data` на `storage`, `bootstrap/cache` |
| `SQLSTATE ... access denied` | корректность `DB_*` в `.env` |
| Графики не грузятся | собран ли `npm run build`; подключён ли `dashboard.js` через `@vite` |
| Ошибки 419 на формах | `APP_URL` и домен совпадают; `SESSION_DOMAIN` корректен |

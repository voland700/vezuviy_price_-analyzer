# Участие в разработке

Спасибо за интерес к проекту Analyzer! Этот документ описывает, как предлагать
изменения.

## С чего начать

1. Ознакомьтесь с [README.md](README.md) и архитектурой в
   [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md), чтобы понимать, как связаны
   Python-парсер и Laravel-приложение.
2. Проверьте открытые [Issues](../../issues) — возможно, тема уже обсуждается.

## Окружение для разработки

```bash
git clone <repo-url> && cd analyzer
composer install
npm install
cp .env.example .env
php artisan key:generate
# настройте DB_* (MySQL, БД analyzer) и выполните миграции
php artisan migrate
npm run dev   # в одном терминале
php artisan serve  # в другом
```

Подробнее — в [docs/WEB-INTERFACE.md](docs/WEB-INTERFACE.md#локальная-разработка)
и [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Процесс работы с кодом

1. Создайте ветку от `main`:
   ```bash
   git checkout -b feature/короткое-описание
   ```
2. Вносите изменения, следуя стилю существующего кода.
3. Проверьте код линтерами и тестами:
   ```bash
   composer test                    # PHPUnit
   ./vendor/bin/pint --test         # стиль PHP (Laravel Pint)
   ```
4. Если меняли фронтенд (`resources/css`, `resources/js`) — соберите ассеты:
   ```bash
   npm run build
   ```
5. Сделайте коммиты с понятными сообщениями (на русском или английском).
6. Откройте Pull Request в `main` и заполните шаблон.

## Соглашения

- **Стиль PHP** — Laravel Pint (PSR-12 + правки Laravel). Запускайте
  `./vendor/bin/pint` для автоисправления.
- **Blade-компоненты** — переиспользуйте существующие (`<x-sortable-th>`,
  `<x-export-button>`, `<x-bool-icon>` и т.д.) вместо дублирования разметки.
- **Контроллеры разделов** — следуйте общему шаблону: трейт `AppliesSorting`,
  методы `buildQuery()` / `index()` / `export()`, whitelist `SORTABLE`.
- **Безопасность** — не коммитьте `.env`, пароли, ключи, реальные URL фидов с
  токенами. Не добавляйте комментарии без необходимости.
- **База данных** — изменения схемы бизнес-таблиц требуют правок **одновременно**
  в миграциях Laravel и в `scripts/analyzer.py` (это единый контракт).

## Сообщения коммитов

Желательно следовать формату `тип: описание`, например:

- `feat: фильтр по бренду в availables`
- `fix: некорректная сортировка по цене`
- `docs: раздел о развёртывании`
- `refactor: вынести сортировку в трейт`

## Сообщение об ошибке / предложение

Откройте Issue и заполните соответствующий шаблон (bug report или feature
request). Приложите шаги воспроизведения, ожидаемое и фактическое поведение,
а также версию PHP и окружения.

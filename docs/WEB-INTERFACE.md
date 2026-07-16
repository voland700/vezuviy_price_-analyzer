# Веб-интерфейс (Laravel)

Документация веб-приложения: маршруты, контроллеры, повторяющиеся функции
(фильтрация, сортировка, экспорт), UI/UX и локальная разработка.

> Документация Python-парсера вынесена в [`../scripts/README.md`](../scripts/README.md).

---

## Содержание

- [Маршруты](#маршруты)
- [Контроллеры](#контроллеры)
- [Общий трейт `AppliesSorting`](#общий-трейт-appliessorting)
- [Фильтрация](#фильтрация)
- [Сортировка](#сортировка)
- [Пагинация](#пагинация)
- [Экспорт в XLSX](#экспорт-в-xlsx)
- [Дашборд](#дашборд)
- [Blade-компоненты](#blade-компоненты)
- [Страницы и разделы](#страницы-и-разделы)
- [Авторизация и тема](#авторизация-и-тема)
- [Локальная разработка](#локальная-разработка)
- [Тестирование](#тестирование)

---

## Маршруты

Определены в `routes/web.php`. Корень `/` делает редирект на `/dashboard`.
Все бизнес-маршруты обёрнуты в группу с middleware `auth` (требуют входа).
Маршруты авторизации (`/login`, `/register`, `/logout`, сброс пароля) — в
`routes/auth.php`.

| Метод | Путь | Контроллер@метод | Имя маршрута | Назначение |
|---|---|---|---|---|
| GET | `/dashboard` | `DashboardController@index` | `dashboard` | дашборд с метриками и графиками |
| GET | `/suppliers` | `SupplierController@index` | `suppliers.index` | список товаров поставщиков |
| GET | `/suppliers/export` | `SupplierController@export` | `suppliers.export` | выгрузка списка в XLSX |
| GET | `/suppliers/by-sku/{sku}` | `SupplierController@showBySku` | `suppliers.by-sku` | карточка товара по SKU |
| GET | `/suppliers/{supplier}` | `SupplierController@show` | `suppliers.show` | карточка товара по id |
| GET | `/availables` | `AvailableController@index` | `availables.index` | список «есть в сайте» |
| GET | `/availables/export` | `AvailableController@export` | `availables.export` | выгрузка в XLSX |
| GET | `/unavailables` | `UnavailableController@index` | `unavailables.index` | список «нет у производителя» |
| GET | `/unavailables/export` | `UnavailableController@export` | `unavailables.export` | выгрузка в XLSX |
| GET | `/undiscovereds` | `UndiscoveredController@index` | `undiscovereds.index` | список «нет в магазине» |
| GET | `/undiscovereds/export` | `UndiscoveredController@export` | `undiscovereds.export` | выгрузка в XLSX |
| GET | `/kits` | `KitController@index` | `kits.index` | список комплектов |
| GET | `/kits/export` | `KitController@export` | `kits.export` | выгрузка комплектов в XLSX |
| GET | `/profile` | `ProfileController@edit` | `profile.edit` | редактирование профиля |
| PATCH | `/profile` | `ProfileController@update` | `profile.update` | обновление профиля |
| DELETE | `/profile` | `ProfileController@destroy` | `profile.destroy` | удаление профиля |

---

## Контроллеры

Структура `app/Http/Controllers/`:

| Файл | Назначение |
|---|---|
| `DashboardController.php` | собирает метрики и данные для графиков дашборда |
| `SupplierController.php` | список/карточка/экспорт товаров поставщиков |
| `AvailableController.php` | список/экспорт товаров «есть в сайте» |
| `UnavailableController.php` | список/экспорт «нет у производителя» |
| `UndiscoveredController.php` | список/экспорт «нет в магазине» |
| `KitController.php` | список/экспорт комплектов |
| `ProfileController.php` | профиль пользователя (Breeze) |
| `Auth/*` | регистрация/вход/сброс пароля (Breeze) |
| `Concerns/AppliesSorting.php` | общий трейт (сортировка + имя файла экспорта) |

Все контроллеры разделов построены по единому шаблону и реализуют три метода:

- `buildQuery(Request)` — строит запрос Eloquent с фильтрами и сортировкой.
  Используется **и для таблицы, и для экспорта** → выгрузка всегда совпадает с
  тем, что видит пользователь.
- `index(Request)` — `buildQuery()->paginate(50)->withQueryString()` + рендер view.
- `export(Request)` — `buildQuery()` без пагинации → скачивание `.xlsx`.

---

## Общий трейт `AppliesSorting`

`app/Http/Controllers/Concerns/AppliesSorting.php` — повторно используемая
логика, подключаемая через `use AppliesSorting;` в каждом контроллере раздела.

Методы трейта:

| Метод | Назначение |
|---|---|
| `applySorting($query, $request, $sortable, $default)` | добавляет `ORDER BY` по разрешённой колонке; колонка сверяется со whitelist `$sortable` |
| `sortMeta($request, $default)` | возвращает текущие `sort`/`direction` для подсветки стрелок в шапке таблицы |
| `exportFilename($table)` | формирует имя файла: `{table}_YYYY-MM-DD_HH-MM-SS.xlsx` |

Параметры сортировки берутся из строки запроса: `?sort=price&direction=desc`.
Если колонка не в whitelist — применяется сортировка по умолчанию (`DEFAULT_SORT`).

---

## Фильтрация

Фильтры передаются как GET-параметры и применяются в `buildQuery()` через
`->when($request->filled(...), ...)`. Пустые значения игнорируются.

Доступные фильтры по разделам:

| Раздел | Поля фильтрации |
|---|---|
| **suppliers** | `name` (вхождение), `sku` (точно), `brand` (select), `category` (select), `available` (да/нет) |
| **availables** | `name` (вхождение), `sku` (точно), `active`, `available`, `info` (`mismatch`/`more`/`less`) |
| **unavailables** | `shop_id`, `name` (вхождение), `sku` (точно), `active`, `available` |
| **undiscovereds** | `sku` (точно), `name` (вхождение), `category` (вхождение), `available`, `brand` (select) |
| **kits** | без фильтров (только сортировка) |

> Параметры фильтра **сохраняются** при переходе по страницам пагинации и при
> экспорте — благодаря `withQueryString()` и построению ссылок экспорта через
`buildQuery()`.

---

## Сортировка

Кликабельные заголовки таблиц реализованы Blade-компонентом `<x-sortable-th>`.
Каждый контроллер объявляет whitelist разрешённых колонок (`SORTABLE`) и
сортировку по умолчанию (`DEFAULT_SORT = 'name'`).

Разрешённые колонки сортировки:

| Раздел | Whitelist |
|---|---|
| **suppliers** | `name, sku, brand, category, available, price` |
| **availables** | `name, active, available, sku, shop_price, supplier_price, info` |
| **unavailables** | `name, active, available, sku, shop_price` |
| **undiscovereds** | `sku, name, category, price, available, brand` |
| **kits** | `name, info` |

Безопасность: колонка сравнивается со whitelist строго (`in_array(..., true)`),
поэтому произвольная колонка через URL проигнорируется (защита от SQL-инъекций
через `ORDER BY`).

---

## Пагинация

- Размер страницы — **50 строк** (`paginate(50)`).
- `withQueryString()` сохраняет параметры фильтра/сортировки в ссылках страниц.
- Вывод навигации — `$rows->links()` (стандартный пагинатор Laravel).

---

## Экспорт в XLSX

Реализован через пакет **maatwebsite/excel** (`composer.json`).
Классы экспорта лежат в `app/Exports/` и реализуют:

- `FromQuery` — источник данных (тот же запрос `buildQuery()`, без пагинации);
- `WithHeadings` — заголовки колонок на русском;
- `WithMapping` — форматирование каждой строки (приведение типов, строковые
  поля и т.п.).

Пример (`app/Exports/SupplierExport.php`):

```php
public function headings(): array
{
    return ['Название', 'SKU', 'Бренд', 'Категория', 'Доступен', 'Склад', 'URL', 'Цена'];
}
```

Скачивание инициируется кнопкой `<x-export-button>` (компонент Blade), которая
ведёт на `*.export` маршрут. Имя файла: `{раздел}_YYYY-MM-DD_HH-MM-SS.xlsx`.

> **Важно:** экспорт применяет те же фильтры и сортировку, что активны в
> таблице на экране — выгрузка соответствует текущей выборке.

---

## Дашборд

`DashboardController@index` → `resources/views/dashboard.blade.php`.

**Виджеты (метрики):**

- Дата обновления данных (берётся как `MAX(created_at)` из `suppliers`;
  подсвечивается красным, если устарела — не сегодняшний прогон).
- Несоответствие цен: `↑ больше` / `↓ меньше` (из `availables.info`).
- Количество товаров, в магазине, нет у производителя, нет в магазине.

**Графики (Chart.js, `resources/js/dashboard.js`):**

- **Бренды производителя** — кольцевая диаграмма (doughnut) распределения
  товаров по брендам.
- **Данные о товарах** — горизонтальная столбчатая диаграмма (bar) сводных
  показателей.

Данные в графики передаются через `data-json` атрибут `<canvas>` (без отдельного
API). Цвета графиков адаптируются под тёмную/светлую тему.

---

## Blade-компоненты

Собственные компоненты в `resources/views/components/`:

| Компонент | Назначение |
|---|---|
| `<x-sortable-th>` | кликабельный заголовок таблицы с индикатором направления сортировки |
| `<x-export-button>` | кнопка экспорта текущей выборки в XLSX |
| `<x-bool-icon>` | иконка-галочка/крестик для булевых полей (`available`, `active`) |
| `<x-price>` | форматированный вывод цены |
| `<x-info-badge>` | бейдж статуса сравнения цен (`больше`/`меньше`) |
| `<x-nav-link>`, `<x-responsive-nav-link>` | пункты навигации с подсветкой активного раздела |

Остальные (`<x-input-label>`, `<x-primary-button>`, `<x-dropdown>`, `<x-modal>`,
...) — стандартные из Laravel Breeze.

---

## Страницы и разделы

Шаблоны в `resources/views/`:

| Путь | Описание |
|---|---|
| `dashboard.blade.php` | главная страница с метриками и графиками |
| `suppliers/index.blade.php` | таблица товаров поставщиков + фильтры |
| `suppliers/show.blade.php` | карточка товара (детали, характеристики, фото) |
| `availables/index.blade.php` | таблица «есть в сайте» |
| `unavailables/index.blade.php` | таблица «нет у производителя» |
| `undiscovereds/index.blade.php` | таблица «нет в магазине» |
| `kits/index.blade.php` | таблица комплектов |
| `layouts/app.blade.php` | основной layout (навигация + контент) |
| `layouts/guest.blade.php` | layout для страниц авторизации (кастомный дизайн входа) |
| `layouts/navigation.blade.php` | верхняя панель навигации |
| `auth/*` | формы входа/регистрации/сброса пароля |
| `profile/*` | редактирование профиля |

---

## Авторизация и тема

- **Авторизация** — Laravel Breeze (Blade-стек). Регистрация, вход, сброс
  пароля, подтверждение пароля, верификация e-mail.
- **Тёмная тема** — переключатель в шапке (Alpine.js, компонент `themeToggle`).
  Класс `dark` навешивается на `<html>` (`layouts/app.blade.php`).
- **Кастомный дизайн страницы входа** — `resources/css/app.css`, классы
  `.login-bg` (фон на весь экран) и `.login-card` (полупрозрачная карточка,
  glassmorphism). Логотип в углу — через именованный слот `logo` в
  `layouts/guest.blade.php`.

---

## Локальная разработка

```bash
# Установка зависимостей
composer install
npm install

# Окружение
cp .env.example .env
php artisan key:generate
# настройте DB_* в .env (MySQL, БД analyzer)

# Миграции
php artisan migrate

# Режим разработки (Vite с горячей перезагрузкой)
npm run dev          # в одном терминале
php artisan serve    # в другом
```

### Сборка фронтенда

| Команда | Назначение |
|---|---|
| `npm run dev` | режим разработки (HMR, автоматически пересобирает при изменениях) |
| `npm run build` | продакшн-сборка в `public/build/` (нужно для прод) |

После правок в `resources/css/app.css` или `resources/js/*` в продакшне всегда
выполняйте `npm run build` — ассеты отдаются из `public/build/` через Vite
manifest.

### Удобная команда dev (всё сразу)

В `composer.json` определён скрипт, запускающий сервер, очередь, логи и Vite
параллельно через `concurrently`:

```bash
composer dev
```

---

## Тестирование

Тесты — PHPUnit (см. `phpunit.xml`, каталог `tests/`).

```bash
composer test
# или напрямую
php artisan test
# или
./vendor/bin/phpunit
```

Стиль кода проверяется **Laravel Pint** (входит в `require-dev`):

```bash
./vendor/bin/pint --test     # проверить
./vendor/bin/pint            # исправить
```

---

## Расширение (добавление нового раздела)

Шаблон для нового раздела данных (по аналогии с существующими):

1. **Миграция + модель** — создайте таблицу и `app/Models/Foo.php` с
   `protected $guarded = [];`.
2. **Экспорт** — класс `app/Exports/FooExport.php` (`FromQuery`, `WithHeadings`,
   `WithMapping`); можно сгенерировать из шаблонов в `stubs/`.
3. **Контроллер** — `app/Http/Controllers/FooController.php` с трейтом
   `AppliesSorting`, методами `buildQuery()`, `index()`, `export()` и константами
   `SORTABLE` / `DEFAULT_SORT`.
4. **Маршруты** — добавьте `index`/`export` в `routes/web.php`.
5. **Views** — `resources/views/foos/index.blade.php` (используйте
   `<x-sortable-th>`, `<x-export-button>`, фильтры, пагинацию).
6. **Навигация** — добавьте ссылку в `layouts/navigation.blade.php`.
7. Пересоберите ассеты (`npm run build`) при необходимости.

# База данных

База данных: **`analyzer`** (MySQL 8.0 / MariaDB 10.x), кодировка `utf8mb4`.

Все таблицы создаются миграциями Laravel в `database/migrations/` и применяются
командой `php artisan migrate`.

Таблицы делятся на две группы:

1. **Служебные Laravel** — `users`, `cache`, `jobs` (+ таблицы сессий и очередей).
2. **Бизнес-таблицы** — `suppliers`, `availables`, `unavailables`,
   `undiscovereds`, `kits`. Их наполняет Python-парсер (`scripts/analyzer.py`),
   а Laravel только читает.

> **Важно:** бизнес-таблицы полностью перезаписываются при каждом прогоне парсера
> (`TRUNCATE` + INSERT). Поэтому рассчитывать на сохранность строк между
> прогонами нельзя — данные обновляются целиком.

---

## Содержание

- [users](#users)
- [suppliers](#suppliers)
- [availables](#availables)
- [unavailables](#unavailables)
- [undiscovereds](#undiscovereds)
- [kits](#kits)
- [Поле `info` (сравнение цен)](#поле-info-сравнение-цен)
- [Контрольные соотношения](#контрольные-соотношения)
- [Полезные SQL-запросы](#полезные-sql-запросы)

---

## users

Пользователи веб-интерфейса (авторизация Laravel Breeze).

| Поле | Тип | Описание |
|---|---|---|
| `id` | bigint unsigned PK | автоинкремент |
| `name` | varchar(255) | имя пользователя |
| `email` | varchar(255), unique | e-mail (для входа) |
| `email_verified_at` | timestamp, nullable | подтверждение e-mail |
| `password` | varchar(255) | хэш пароля (bcrypt) |
| `remember_token` | varchar(100), nullable | «запомнить меня» |
| `created_at`, `updated_at` | timestamp | временные метки |

---

## suppliers

Все товары всех поставщиков (полный каталог производителей).

| Поле | Тип | Описание |
|---|---|---|
| `id` | bigint unsigned PK | автоинкремент |
| `name` | varchar(255) | название товара (`<name>`) |
| `sku` | varchar(255) | артикул (`<vendorCode>`), без концевых пробелов |
| `brand` | varchar(255), nullable | бренд поставщика (`brand_name` из конфига) |
| `category` | varchar(255), nullable | название категории поставщика |
| `available` | tinyint(1), default 0 | доступность к отгрузке (`<offer available>`) |
| `store` | tinyint(1), default 0 | наличие на складе (`<store>`) |
| `url` | varchar(1000), nullable | ссылка на товар |
| `price` | int, default 0 | цена поставщика |
| `pictures` | json, nullable | JSON-массив ссылок на изображения |
| `description` | text, nullable | описание товара |
| `params` | json, nullable | JSON-массив характеристик `[{name, value}, ...]` |
| `created_at`, `updated_at` | timestamp | временные метки прогона |

> В Laravel поле `params`/`pictures` приводится к массиву через `$casts`
> (`app/Models/Supplier.php`).

---

## availables

Товары, которые есть **и в магазине, и у поставщика** (`S ∩ M`), с прямым
сравнением цен.

| Поле | Тип | Описание |
|---|---|---|
| `id` | bigint unsigned PK | автоинкремент |
| `shop_id` | int | ID товара в каталоге магазина |
| `name` | varchar(255) | название товара в магазине |
| `active` | tinyint(1), default 0 | активность товара в магазине |
| `available` | tinyint(1), default 0 | доступность у поставщика |
| `sku` | varchar(255) | SKU товара |
| `shop_price` | int, default 0 | цена в магазине |
| `supplier_price` | int, default 0 | цена у поставщика |
| `info` | varchar(255), nullable | результат сравнения цен: `больше` / `меньше` / `NULL` |
| `created_at`, `updated_at` | timestamp | временные метки |

---

## unavailables

Товары магазина, **отсутствующие у поставщика** (`M − S`).

| Поле | Тип | Описание |
|---|---|---|
| `id` | bigint unsigned PK | автоинкремент |
| `shop_id` | int | ID товара в каталоге магазина |
| `name` | varchar(255) | название товара в магазине |
| `active` | tinyint(1), default 0 | активность товара в магазине |
| `available` | tinyint(1), default 0 | всегда `0` (товара нет у поставщика) |
| `sku` | varchar(255) | SKU товара |
| `shop_price` | int, default 0 | цена в магазине |
| `created_at`, `updated_at` | timestamp | временные метки |

---

## undiscovereds

Товары поставщика, **отсутствующие в магазине** (`S − M`) — потенциальные
новинки ассортимента.

| Поле | Тип | Описание |
|---|---|---|
| `id` | bigint unsigned PK | автоинкремент |
| `sku` | varchar(255) | артикул (`<vendorCode>`) |
| `name` | varchar(255) | название товара |
| `category` | varchar(255), nullable | категория поставщика |
| `price` | int, default 0 | цена поставщика |
| `available` | tinyint(1), default 0 | доступность к отгрузке |
| `brand` | varchar(255), nullable | бренд поставщика |
| `url` | varchar(1000), nullable | ссылка на товар |
| `created_at`, `updated_at` | timestamp | временные метки |

---

## kits

Товары-комплекты магазина: расчёт суммарной стоимости компонентов у поставщика
и сравнение с ценой комплекта в магазине.

| Поле | Тип | Описание |
|---|---|---|
| `id` | bigint unsigned PK | автоинкремент |
| `shop_id` | int | `id` товара-комплекта в каталоге магазина |
| `name` | varchar(255) | название комплекта |
| `sku_1` | varchar(255), nullable | SKU первого компонента |
| `sku_2` | varchar(255), nullable | SKU второго компонента |
| `price_1` | int, default 0 | цена первого компонента у поставщика |
| `price_2` | int, default 0 | цена второго компонента у поставщика |
| `shop_price` | int, default 0 | цена комплекта в магазине |
| `supplier_price` | int, default 0 | сумма компонентов (`price_1 + price_2`) |
| `info` | varchar(255), nullable | сравнение `supplier_price` и `shop_price`: `больше` / `меньше` / `NULL` |
| `created_at`, `updated_at` | timestamp | временные метки |

---

## Поле `info` (сравнение цен)

Используется в `availables` и `kits`. Логика одна и та же:

| Условие | Значение `info` | Что значит |
|---|---|---|
| `supplier_price == shop_price` | `NULL` | цены совпадают |
| `supplier_price > shop_price` | `больше` | цена поставщика **выше** |
| `supplier_price < shop_price` | `меньше` | цена поставщика **ниже** |

> На странице «Есть в сайте» в фильтре по `info` доступны значения:
> `mismatch` (любое расхождение), `more` (`больше`), `less` (`меньше`).

---

## Контрольные соотношения

Используются для проверки корректности прогона парсера:

- `availables + unavailables = всего товаров магазина`
- `availables + undiscovereds = всего уникальных SKU поставщиков`
- `kits ≤ len(KITS_DATA)` — число строк не превышает количество описанных
  комплектов (пропущенные фиксируются в логе уровня `WARNING`).

---

## Полезные SQL-запросы

```sql
-- дата последнего обновления данных (время прогона парсера)
SELECT MAX(created_at) AS last_update FROM suppliers;

-- распределение результата сравнения цен (availables)
SELECT IFNULL(info, 'равно') AS info, COUNT(*) AS cnt
FROM availables
GROUP BY info;

-- товары, где цена поставщика выше цены магазина
SELECT shop_id, sku, shop_price, supplier_price, info
FROM availables
WHERE info = 'больше'
ORDER BY (supplier_price - shop_price) DESC;

-- распределение по брендам
SELECT brand, COUNT(*) AS cnt
FROM suppliers
WHERE brand IS NOT NULL AND brand <> ''
GROUP BY brand
ORDER BY cnt DESC;

-- новинки поставщика, отсутствующие в магазине
SELECT sku, name, brand, price
FROM undiscovereds
ORDER BY brand, price DESC;

-- комплекты с расхождением стоимости
SELECT name, price_1, price_2, supplier_price, shop_price, info
FROM kits
WHERE info IS NOT NULL;

-- пример характеристик товара (JSON)
SELECT name, JSON_PRETTY(params) AS params
FROM suppliers
WHERE sku = '00000017469';
```

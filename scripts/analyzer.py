#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
analyzer.py
Парсинг XML данных поставщиков и JSON данных интернет-магазина,
сравнение по SKU и запись результатов в MySQL/MariaDB (БД analyzer).

Таблицы:
  suppliers      — все товары поставщиков
  availables     — товары, которые есть и в магазине, и у поставщика (+ сравнение цен)
  unavailables   — товары магазина, отсутствующие у поставщика
  undiscovereds  — товары поставщика, отсутствующие в магазине
  kits           — товары-комплекты магазина: расчёт и сравнение суммарной
                   стоимости компонентов у поставщика с ценой комплекта в магазине
"""
import json
import logging
import sys
import time
import xml.etree.ElementTree as ET
from datetime import datetime
from pathlib import Path

import requests
import pymysql

PROJECT_ROOT = Path(__file__).resolve().parent.parent
ENV_FILE = PROJECT_ROOT / ".env"

SHOP_URL = "https://www.pechnik.su/get-data-vezuviy/"

BRANDS = [
    {"brand_name": "Везувий", "brand_url": "https://vezuviy.su/yml_get/5"},
    {"brand_name": "ETNA", "brand_url": "https://etna-pech.ru/yml_get/7"},
    {"brand_name": "Эверест", "brand_url": "https://everest-pech.com/yml_get/6"},
    {"brand_name": "ASTON", "brand_url": "https://aston-pech.ru/yml_get/xl6wvlbtjpvk"},
]

HTTP_TIMEOUT = 30
BATCH_SIZE = 500
USER_AGENT = "Mozilla/5.0 (analyzer-script)"

KITS_DATA = [
    {'id': 5012, 'name': 'Казан 12л. с крышкой', 'code_first': '00000002791', 'code_second': '00000002793'},
    {'id': 5009, 'name': 'Казан 12л. с ручкой', 'code_first': '00000005578', 'code_second': '00000002793'},
    {'id': 5010, 'name': 'Казан 6л. с крышкой', 'code_first': '00000008544', 'code_second': '00000006908'},
    {'id': 5007, 'name': 'Казан 6л. с ручкой', 'code_first': '00000008549', 'code_second': '00000006908'},
    {'id': 5011, 'name': 'Казан 8л. с крышкой', 'code_first': '00000002792', 'code_second': '00000002794'},
    {'id': 5008, 'name': 'Казан 8л. с ручкой', 'code_first': '00000005579', 'code_second': '00000002794'},
    {'id': 5143, 'name': 'Сербский Казан 14 л.', 'code_first': '00000011714', 'code_second': '00000011396'},
    {'id': 4907, 'name': 'Сковорода чугунная Везувий 6л Ø 380мм', 'code_first': '00000008795', 'code_second': '00000008802'},
    {'id': 4997, 'name': 'Сковорода чугунная Везувий 9л Ø 455мм', 'code_first': '00000011271', 'code_second': '00000011293'},
]

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
)
log = logging.getLogger("analyzer")


def load_env(path: Path) -> dict:
    env = {}
    if not path.exists():
        return env
    for line in path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, val = line.split("=", 1)
        env[key.strip()] = val.strip().strip('"').strip("'")
    return env


def to_bool(val) -> int:
    if val is None:
        return 0
    return 1 if str(val).strip().lower() == "true" else 0


def fetch(url: str) -> bytes:
    log.info("GET %s", url)
    resp = requests.get(url, timeout=HTTP_TIMEOUT, headers={"User-Agent": USER_AGENT})
    resp.raise_for_status()
    return resp.content


def parse_shop(text: bytes) -> dict:
    raw = json.loads(text)
    data = raw.get("data", raw) if isinstance(raw, dict) else raw
    shop = {}
    for key, item in (data or {}).items():
        sku = str(item.get("sku", key)).strip()
        if not sku:
            continue
        shop[sku] = {
            "id": int(item.get("id", 0) or 0),
            "name": str(item.get("name", "")).strip(),
            "active": 1 if int(item.get("active", 0) or 0) == 1 else 0,
            "sku": sku,
            "price": int(item.get("price", 0) or 0),
        }
    return shop


def parse_supplier(text: bytes, brand_name: str):
    root = ET.fromstring(text)

    categories = {}
    for cat in root.findall(".//categories/category"):
        cid = (cat.get("id") or "").strip()
        if cid:
            categories[cid] = (cat.text or "").strip()

    rows = []
    index = {}
    for offer in root.findall(".//offers/offer"):
        try:
            category_id = (offer.findtext("categoryId") or "").strip()
            sku = (offer.findtext("vendorCode") or "").strip()
            price = int(float((offer.findtext("price") or "0").strip() or 0))
            name = (offer.findtext("name") or "").strip()
            url = (offer.findtext("url") or "").strip()
            description = (offer.findtext("description") or "").strip()

            pictures = []
            for pic in offer.findall("picture"):
                p = (pic.text or "").strip()
                if p:
                    pictures.append(p)

            params = []
            for p in offer.findall("param"):
                pname = (p.get("name") or "").strip()
                pvalue = (p.text or "").strip()
                if pname:
                    params.append({"name": pname, "value": pvalue})

            category_name = categories.get(category_id, "")
            record = {
                "name": name,
                "sku": sku,
                "brand": brand_name,
                "category": category_name,
                "available": to_bool(offer.get("available")),
                "store": to_bool(offer.findtext("store")),
                "url": url,
                "price": price,
                "pictures": json.dumps(pictures, ensure_ascii=False),
                "description": description,
                "params": json.dumps(params, ensure_ascii=False),
            }
            rows.append(record)
            if sku:
                index[sku] = {
                    "name": name,
                    "brand": brand_name,
                    "category": category_name,
                    "available": record["available"],
                    "price": price,
                    "url": url,
                }
        except Exception as exc:
            log.warning("[%s] пропуск offer id=%s: %s",
                        brand_name, offer.get("id"), exc)
    return rows, index


def connect(env: dict):
    return pymysql.connect(
        host=env.get("DB_HOST", "localhost"),
        port=int(env.get("DB_PORT", 3306) or 3306),
        user=env.get("DB_USERNAME"),
        password=env.get("DB_PASSWORD"),
        database=env.get("DB_DATABASE"),
        charset="utf8mb4",
        autocommit=False,
    )


def truncate(conn, tables):
    with conn.cursor() as cur:
        cur.execute("SET FOREIGN_KEY_CHECKS=0")
        for t in tables:
            cur.execute("TRUNCATE TABLE `%s`" % t)
        cur.execute("SET FOREIGN_KEY_CHECKS=1")
    conn.commit()


def batch_insert(conn, table: str, columns: list, rows: list):
    if not rows:
        log.info("  %s: 0 строк", table)
        return
    cols_sql = "`" + "`,`".join(columns) + "`"
    placeholder = "(" + ",".join(["%s"] * len(columns)) + ")"
    sql = "INSERT INTO `%s` (%s) VALUES %s" % (table, cols_sql, placeholder)
    with conn.cursor() as cur:
        for i in range(0, len(rows), BATCH_SIZE):
            chunk = [tuple(r[c] for c in columns) for r in rows[i:i + BATCH_SIZE]]
            cur.executemany(sql, chunk)
        conn.commit()
    log.info("  %s: вставлено %d строк", table, len(rows))


def compare_info(supplier_price: int, shop_price: int):
    if supplier_price == shop_price:
        return None
    return "больше" if supplier_price > shop_price else "меньше"


def main():
    started = time.time()
    env = load_env(ENV_FILE)
    if not env.get("DB_DATABASE"):
        log.error("DB_* не найдены в .env (%s)", ENV_FILE)
        sys.exit(1)

    shop = parse_shop(fetch(SHOP_URL))
    log.info("Магазин: %d товаров", len(shop))
    shop_by_id = {v["id"]: v for v in shop.values()}

    all_rows = []
    supplier_idx = {}
    for brand in BRANDS:
        rows, idx = parse_supplier(fetch(brand["brand_url"]), brand["brand_name"])
        log.info("  %s: %d offers", brand["brand_name"], len(rows))
        all_rows.extend(rows)
        supplier_idx.update(idx)
    log.info("Поставщики: %d offers, уникальных SKU: %d",
             len(all_rows), len(supplier_idx))

    shop_skus = set(shop.keys())
    sup_skus = set(supplier_idx.keys())

    availables = []
    for sku in shop_skus & sup_skus:
        s = shop[sku]
        sup = supplier_idx[sku]
        availables.append({
            "shop_id": s["id"],
            "name": s["name"],
            "active": s["active"],
            "available": sup["available"],
            "sku": sku,
            "shop_price": s["price"],
            "supplier_price": sup["price"],
            "info": compare_info(sup["price"], s["price"]),
        })

    unavailables = []
    for sku in shop_skus - sup_skus:
        s = shop[sku]
        unavailables.append({
            "shop_id": s["id"],
            "name": s["name"],
            "active": s["active"],
            "available": 0,
            "sku": sku,
            "shop_price": s["price"],
        })

    undiscovereds = []
    for sku in sup_skus - shop_skus:
        sup = supplier_idx[sku]
        undiscovereds.append({
            "sku": sku,
            "name": sup["name"],
            "category": sup["category"],
            "price": sup["price"],
            "available": sup["available"],
            "brand": sup["brand"],
            "url": sup["url"],
        })

    log.info("Сравнение: availables=%d  unavailables=%d  undiscovereds=%d",
             len(availables), len(unavailables), len(undiscovereds))

    kits = []
    for k in KITS_DATA:
        shop_id = k["id"]
        sku_1 = k["code_first"]
        sku_2 = k["code_second"]

        shop_item = shop_by_id.get(shop_id)
        sup_1 = supplier_idx.get(sku_1)
        sup_2 = supplier_idx.get(sku_2)

        if shop_item is None:
            log.warning("kits: товар магазина id=%s не найден, пропуск", shop_id)
            continue
        if sup_1 is None or sup_2 is None:
            log.warning("kits: комплект id=%s: компонент поставщика не найден "
                        "(sku_1=%s found=%s, sku_2=%s found=%s), пропуск",
                        shop_id, sku_1, sup_1 is not None, sku_2, sup_2 is not None)
            continue

        price_1 = sup_1["price"]
        price_2 = sup_2["price"]
        supplier_price = price_1 + price_2
        shop_price = shop_item["price"]
        kits.append({
            "shop_id": shop_id,
            "name": k["name"],
            "sku_1": sku_1,
            "sku_2": sku_2,
            "price_1": price_1,
            "price_2": price_2,
            "shop_price": shop_price,
            "supplier_price": supplier_price,
            "info": compare_info(supplier_price, shop_price),
        })
    log.info("Комплекты: %d", len(kits))

    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    conn = connect(env)
    try:
        log.info("Очистка таблиц...")
        truncate(conn, ["suppliers", "availables", "unavailables", "undiscovereds", "kits"])

        for r in all_rows:
            r["created_at"] = now
            r["updated_at"] = now
        batch_insert(conn, "suppliers",
                     ["name", "sku", "brand", "category", "available", "store",
                      "url", "price", "pictures", "description", "params",
                      "created_at", "updated_at"],
                     all_rows)

        for r in availables:
            r["created_at"] = now
            r["updated_at"] = now
        batch_insert(conn, "availables",
                     ["shop_id", "name", "active", "available", "sku",
                      "shop_price", "supplier_price", "info",
                      "created_at", "updated_at"],
                     availables)

        for r in unavailables:
            r["created_at"] = now
            r["updated_at"] = now
        batch_insert(conn, "unavailables",
                     ["shop_id", "name", "active", "available", "sku",
                      "shop_price", "created_at", "updated_at"],
                     unavailables)

        for r in undiscovereds:
            r["created_at"] = now
            r["updated_at"] = now
        batch_insert(conn, "undiscovereds",
                     ["sku", "name", "category", "price", "available", "brand",
                      "url", "created_at", "updated_at"],
                     undiscovereds)

        for r in kits:
            r["created_at"] = now
            r["updated_at"] = now
        batch_insert(conn, "kits",
                     ["shop_id", "name", "sku_1", "sku_2", "price_1", "price_2",
                      "shop_price", "supplier_price", "info",
                      "created_at", "updated_at"],
                     kits)
    finally:
        conn.close()

    log.info("Готово за %.1f c", time.time() - started)


if __name__ == "__main__":
    main()

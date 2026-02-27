#!/usr/bin/env python3
"""
Script d’orchestration :
- Scrape les commandes
- Synchronise la base (orders + items)
- Logue le résultat
"""

import sys
import os

# Chemin absolu vers la racine du projet
BASE_DIR = os.path.dirname(os.path.abspath(__file__))          # /beuchat/script
ROOT_DIR = os.path.dirname(BASE_DIR)                           # /beuchat
SRC_DIR = os.path.join(ROOT_DIR, "src")                        # /beuchat/src

if SRC_DIR not in sys.path:
    sys.path.insert(0, SRC_DIR)

from beuchat_reception.db import Database
from beuchat_reception.repository.order_repository import OrderRepository
from beuchat_reception.scraper import Scraper  # à adapter selon ton module réel
import traceback


def main():
    db = Database()
    conn = db.connect()

    repo = OrderRepository(db)
    scraper = Scraper()

    try:
        print("🔍 Scraping en cours…")
        orders = scraper.get_orders()  # Doit retourner une liste de {header, items}

        print(f"📦 {len(orders)} commandes récupérées")

        count = 0
        for order in orders:
            repo.save_full_order(order["header"], order["items"])
            count += 1

        db.log_scrape("success", f"{count} commandes synchronisées")
        print(f"✅ Synchronisation terminée : {count} commandes")

    except Exception as e:
        error_msg = traceback.format_exc()
        db.log_scrape("error", error_msg)
        print("❌ Erreur lors du scraping / synchronisation")
        print(error_msg)

    finally:
        db.close()
        print("🔌 Connexion DB fermée")


if __name__ == "__main__":
    main()

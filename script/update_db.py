#!/usr/bin/env python3
"""
Script d’orchestration :
- Scrape les commandes
- Synchronise la base (orders + items)
- Logue le résultat (stats détaillées)
"""

import sys
import os
import json
import traceback

# Chemin absolu vers la racine du projet
BASE_DIR = os.path.dirname(os.path.abspath(__file__))          # /beuchat/script
ROOT_DIR = os.path.dirname(BASE_DIR)                           # /beuchat
SRC_DIR = os.path.join(ROOT_DIR, "src")                        # /beuchat/src

if SRC_DIR not in sys.path:
    sys.path.insert(0, SRC_DIR)

from beuchat_reception.db import Database
from beuchat_reception.repository.order_repository import OrderRepository
from beuchat_reception.scraper import Scraper


def main():
    db = Database()
    conn = db.connect()

    repo = OrderRepository(db)
    scraper = Scraper()

    try:
        print(" Scraping en cours…")
        orders = scraper.get_orders()

        print(f" {len(orders)} commandes récupérées")

        # Statistiques globales
        stats = {
            "orders_added": 0,
            "orders_updated": 0,
            "items_added": 0,
            "items_updated": 0,
            "items_deleted": 0,
            "items_restored": 0
        }

        # Synchronisation
        for order in orders:
            result = repo.save_full_order(order["header"], order["items"])

            if result["order_added"]:
                stats["orders_added"] += 1
            else:
                stats["orders_updated"] += 1

            stats["items_added"] += result["items_added"]
            stats["items_updated"] += result["items_updated"]
            stats["items_deleted"] += result["items_deleted"]
            stats["items_restored"] += result["items_restored"]

        # Log JSON dans scrape_logs
        db.log_scrape("success", json.dumps(stats))

        # Affichage console
        print(" Synchronisation terminée")
        print(f"   Commandes ajoutées : {stats['orders_added']}")
        print(f"   Commandes mises à jour : {stats['orders_updated']}")
        print(f"   Items ajoutés : {stats['items_added']}")
        print(f"   Items mis à jour : {stats['items_updated']}")
        print(f"   Items supprimés : {stats['items_deleted']}")
        print(f"   Items restaurés : {stats['items_restored']}")

    except Exception as e:
        error_msg = traceback.format_exc()
        db.log_scrape("error", error_msg)
        print(" Erreur lors du scraping / synchronisation")
        print(error_msg)

    finally:
        db.close()
        print(" Connexion DB fermée")


if __name__ == "__main__":
    main()

import json
import os
import mysql.connector
from mysql.connector import Error
from pathlib import Path


class Database:
    def __init__(self, config_path=None):
        """
        Initialise la connexion en lisant config.php converti en JSON.
        """

        # Détection automatique si aucun chemin n'est fourni
        if config_path is None:
            root = Path(__file__).resolve().parents[2]
            config_path = root / "config.json.php"

        self.config = self._load_php_config(config_path)
        self.conn = None

    # ---------------------------------------------------------
    # Chargement de la config PHP
    # ---------------------------------------------------------
    def _load_php_config(self, path):
        """
        Exécute config.json.php et récupère la configuration JSON.
        """
        if not Path(path).exists():
            raise FileNotFoundError(f"Config file not found: {path}")

        # Exécution du fichier PHP
        import subprocess

        result = subprocess.run(
            ["php", str(path)],
            capture_output=True,
            text=True
        )

        if result.returncode != 0:
            raise RuntimeError(f"PHP config error: {result.stderr}")

        return json.loads(result.stdout)

    # ---------------------------------------------------------
    # Connexion MySQL
    # ---------------------------------------------------------
    def connect(self):
        """
        Établit la connexion MySQL.
        """
        try:
            self.conn = mysql.connector.connect(
                host=self.config["db_host"],
                user=self.config["db_user"],
                password=self.config["db_pass"],
                database=self.config["db_name"],
                charset="utf8mb4"
            )
            return self.conn

        except Error as e:
            raise RuntimeError(f"Database connection error: {e}")

    # ---------------------------------------------------------
    # Insertion d'une commande
    # ---------------------------------------------------------
    def save_order(self, order):
        """
        order = {
            "numero": "...",
            "contact": "...",
            "date": "2026-02-18",
            "statut": "...",
            "facture": "F26-00935"
        }
        """
        sql = """
            INSERT INTO orders (numero, contact, date_commande, statut, facture)
            VALUES (%s, %s, %s, %s, %s)
        """

        values = (
            order["numero"],
            order.get("contact"),
            order.get("date"),
            order.get("statut"),
            order.get("facture"),
        )

        cursor = self.conn.cursor()
        cursor.execute(sql, values)
        self.conn.commit()

        return cursor.lastrowid

    # ---------------------------------------------------------
    # Insertion d'un produit
    # ---------------------------------------------------------
    def save_order_item(self, order_id, item):
        """
        item = {
            "reference": "...",
            "nom": "...",
            "quantite": "1.00",
            "statut": "Facturé"
        }
        """
        sql = """
            INSERT INTO order_items (order_id, reference, nom, quantite_commandee, statut)
            VALUES (%s, %s, %s, %s, %s)
        """

        values = (
            order_id,
            item["reference"],
            item["nom"],
            item["quantite"],
            item.get("statut"),
        )

        cursor = self.conn.cursor()
        cursor.execute(sql, values)
        self.conn.commit()

        return cursor.lastrowid

    # ---------------------------------------------------------
    # Log du scraper
    # ---------------------------------------------------------
    def log_scrape(self, status, message):
        sql = """
            INSERT INTO scrape_logs (status, message)
            VALUES (%s, %s)
        """

        cursor = self.conn.cursor()
        cursor.execute(sql, (status, message))
        self.conn.commit()

    # ---------------------------------------------------------
    # Fermeture
    # ---------------------------------------------------------
    def close(self):
        if self.conn:
            self.conn.close()

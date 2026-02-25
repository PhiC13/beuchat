import json
import subprocess
from pathlib import Path
import mysql.connector
from mysql.connector import Error


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
    # COMMANDES
    # ---------------------------------------------------------
    def get_order_by_numero(self, numero):
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM orders WHERE numero = %s", (numero,))
        return cursor.fetchone()

    def insert_order(self, header):
        cursor = self.conn.cursor()
        sql = """
            INSERT INTO orders (numero, contact, date_commande, statut, facture)
            VALUES (%s, %s, %s, %s, %s)
        """
        cursor.execute(sql, (
            header["numero"],
            header.get("contact"),
            header.get("date"),
            header.get("statut"),
            header.get("facture"),
        ))
        self.conn.commit()
        return cursor.lastrowid

    def update_order(self, order_id, header):
        cursor = self.conn.cursor()
        sql = """
            UPDATE orders
            SET contact=%s, date_commande=%s, statut=%s, facture=%s, updated_at=NOW()
            WHERE id=%s
        """
        cursor.execute(sql, (
            header.get("contact"),
            header.get("date"),
            header.get("statut"),
            header.get("facture"),
            order_id
        ))
        self.conn.commit()

    # ---------------------------------------------------------
    # ITEMS
    # ---------------------------------------------------------
    def get_item(self, order_id, reference):
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT * FROM order_items
            WHERE order_id = %s AND reference = %s
        """, (order_id, reference))
        return cursor.fetchone()

    def insert_item(self, order_id, item):
        cursor = self.conn.cursor()
        sql = """
            INSERT INTO order_items
            (order_id, reference, nom, quantite_commandee, statut)
            VALUES (%s, %s, %s, %s, %s)
        """
        cursor.execute(sql, (
            order_id,
            item["reference"],
            item["nom"],
            item["quantite"],
            item.get("statut"),
        ))
        self.conn.commit()
        return cursor.lastrowid

    def update_item(self, item_id, item):
        cursor = self.conn.cursor()
        sql = """
            UPDATE order_items
            SET nom=%s,
                quantite_commandee=%s,
                statut=%s,
                updated_at=NOW()
            WHERE id=%s
        """
        cursor.execute(sql, (
            item["nom"],
            item["quantite"],
            item.get("statut"),
            item_id
        ))
        self.conn.commit()

    def mark_item_deleted(self, item_id):
        cursor = self.conn.cursor()
        cursor.execute("""
            UPDATE order_items
            SET deleted_at = NOW()
            WHERE id = %s AND deleted_at IS NULL
        """, (item_id,))
        self.conn.commit()

    def restore_item(self, item_id):
        cursor = self.conn.cursor()
        cursor.execute("""
            UPDATE order_items
            SET deleted_at = NULL
            WHERE id = %s
        """, (item_id,))
        self.conn.commit()

    # ---------------------------------------------------------
    # LOGS
    # ---------------------------------------------------------
    def log_scrape(self, status, message):
        cursor = self.conn.cursor()
        sql = """
            INSERT INTO scrape_logs (status, message)
            VALUES (%s, %s)
        """
        cursor.execute(sql, (status, message))
        self.conn.commit()

    # ---------------------------------------------------------
    # Fermeture
    # ---------------------------------------------------------
    def close(self):
        if self.conn:
            self.conn.close()

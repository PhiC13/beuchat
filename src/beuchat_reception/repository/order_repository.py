from datetime import datetime


def convert_date_fr_to_mysql(date_str):
    """
    Convertit '18/02/2026' → '2026-02-18'
    """
    return datetime.strptime(date_str, "%d/%m/%Y").strftime("%Y-%m-%d")


class OrderRepository:
    def __init__(self, db):
        self.db = db

    # ---------------------------------------------------------
    # Sauvegarde complète d'une commande + items
    # ---------------------------------------------------------
    def save_full_order(self, header, items):
        """
        header = { numero, contact, date, statut, facture }
        items = [ { reference, nom, quantite, statut }, ... ]
        """

        # 0) Convertir la date FR → MySQL
        if header.get("date"):
            header["date"] = convert_date_fr_to_mysql(header["date"])

        # 1) Vérifier si la commande existe déjà
        existing = self.db.get_order_by_numero(header["numero"])

        if existing:
            order_id = existing["id"]
            self.db.update_order(order_id, header)
        else:
            order_id = self.db.insert_order(header)

        # 2) Synchroniser les items
        self._sync_items(order_id, items)

    # ---------------------------------------------------------
    # Synchronisation des items
    # ---------------------------------------------------------
    def _sync_items(self, order_id, scraped_items):
        """
        Compare les items scrapés avec ceux en DB :
        - nouveaux → insert
        - modifiés → update
        - disparus → mark deleted
        - réapparus → restore
        """

        # Indexation des items scrapés par référence
        scraped_by_ref = {item["reference"]: item for item in scraped_items}

        # Récupérer les items existants
        cursor = self.db.conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM order_items WHERE order_id = %s", (order_id,))
        existing_items = cursor.fetchall()

        existing_by_ref = {item["reference"]: item for item in existing_items}

        # 1) Items nouveaux ou modifiés
        for ref, scraped in scraped_by_ref.items():
            if ref not in existing_by_ref:
                # nouvel item
                self.db.insert_item(order_id, scraped)
            else:
                existing = existing_by_ref[ref]

                # item supprimé précédemment → restaurer
                if existing["deleted_at"] is not None:
                    self.db.restore_item(existing["id"])

                # item modifié ?
                if (
                    scraped["nom"] != existing["nom"]
                    or scraped["quantite"] != str(existing["quantite_commandee"])
                    or scraped.get("statut") != existing.get("statut")
                ):
                    self.db.update_item(existing["id"], scraped)

        # 2) Items disparus → mark deleted
        for ref, existing in existing_by_ref.items():
            if ref not in scraped_by_ref:
                self.db.mark_item_deleted(existing["id"])

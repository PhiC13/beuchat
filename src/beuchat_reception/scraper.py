from beuchat_reception.auth.client import create_session
from beuchat_reception.parsing.orders import extract_orders
from beuchat_reception.parsing.products import extract_product_details


class Scraper:
    """
    Scraper unifié qui retourne les commandes + items
    dans un format exploitable par le repository.
    """

    BASE_URL = "https://mybeuchatpro.beuchat-diving.com/fr-fr/my-orders/order-tracking"

    def __init__(self):
        self.session = create_session()

    def get_orders(self):
        """
        Retourne une liste de commandes sous la forme :
        [
            {
                "header": {...},
                "items": [...]
            },
            ...
        ]
        """

        # 1. Récupération HTML principal
        resp = self.session.get(self.BASE_URL)
        html = resp.text

        # 2. Extraction des commandes (header)
        headers = extract_orders(self.session)

        # 3. Extraction des produits (items)
        details = extract_product_details(self.session, html)

        # 4. Regroupement par numéro de commande
        orders_by_num = {}

        for h in headers:
            num = h["numero"]
            orders_by_num[num] = {
                "header": h,
                "items": []
            }

        for item in details:
            num = item["commande"]
            if num in orders_by_num:
                orders_by_num[num]["items"].append({
                    "reference": item["reference"],
                    "nom": item["nom"],
                    "quantite": item["quantite"],
                    "statut": item["statut"],
                    "facture": item["facture"],
                })

        # 5. Retour sous forme de liste
        return list(orders_by_num.values())

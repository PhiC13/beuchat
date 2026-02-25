from bs4 import BeautifulSoup

BASE_URL = "https://mybeuchatpro.beuchat-diving.com"
ORDERS_URL = BASE_URL + "/fr-fr/my-orders/order-tracking"


def extract_orders(session):
    """Récupère et parse la liste des commandes Beuchat."""
    resp = session.get(ORDERS_URL)
    soup = BeautifulSoup(resp.text, "html.parser")

    orders = []

    # Chaque commande est dans un bloc .collapsed
    blocks = soup.select("div.collapsed")

    for block in blocks:
        # Numéro de commande
        numero = block.select_one("h5.title")
        numero = numero.get_text(strip=True) if numero else None

        # Contact / libellé
        contact = block.select_one("h5 small.text-gray")
        contact = contact.get_text(strip=True) if contact else None

        # Date mobile
        date_mobile = block.select_one("h5.d-block small.text-gray")
        # Date desktop
        date_desktop = block.select_one("div.d-none.d-lg-block small.text-gray")

        if date_mobile:
            date = date_mobile.get_text(strip=True).replace("Saisie le ", "")
        elif date_desktop:
            date = date_desktop.get_text(strip=True)
        else:
            date = None

        # Statut
        statut = block.select_one("div.text-center small.text-default")
        statut = statut.get_text(strip=True) if statut else None

        orders.append({
            "numero": numero,
            "contact": contact,
            "date": date,
            "statut": statut
        })

    return orders

import re
from bs4 import BeautifulSoup

BASE_URL = "https://mybeuchatpro.beuchat-diving.com"


def extract_product_details(session, html):
    soup = BeautifulSoup(html, "html.parser")
    results = []

    blocks = soup.select("div.collapsed")

    for block in blocks:
        # Numéro de commande
        numero_el = block.select_one("h5.title")
        numero = numero_el.get_text(strip=True) if numero_el else None

        # Statut de la commande (fallback)
        order_status_el = block.select_one("div.text-center small.text-default")
        order_status = order_status_el.get_text(strip=True) if order_status_el else None

        # ID interne
        target = block.get("data-target")
        if not target:
            continue

        m = re.search(r"(\d+)$", target)
        if not m:
            continue
        internal_id = m.group(1)

        ajax_url = f"{BASE_URL}/fr-fr/my-orders/order-tracking/products/{internal_id}"
        resp = session.get(ajax_url)
        detail_soup = BeautifulSoup(resp.text, "html.parser")

        # Facture (si présente)
        facture = None
        facture_block = detail_soup.select_one(".primary_border_radius_right")
        if facture_block:
            facture = facture_block.get_text(strip=True)

        container = detail_soup.select_one(f"#orderProducts{internal_id}")
        if not container:
            continue

        # Chaque produit est un .row dans ce container
        for row in container.select("div.row"):
            # Référence
            ref_el = row.select_one("h6")
            reference = None
            if ref_el:
                txt = ref_el.get_text(strip=True)
                reference = txt.replace("Réf. :", "").strip()

            # Nom
            name_el = row.select_one("h5 b")
            nom = name_el.get_text(strip=True) if name_el else None

            # Quantité
            qty_el = row.select_one(".quantity")
            quantite = qty_el.get_text(strip=True) if qty_el else None

            # Statut produit (dans la zone prix)
            status_el = row.select_one(".price-wrapper small.text-gray")
            statut = status_el.get_text(strip=True) if status_el else None

            # Nettoyage : si on a récupéré "Quantité" comme statut, on le remplace par le statut de commande
            if statut and statut.lower().startswith("quantité"):
                statut = order_status

            # Ignorer les lignes qui ne sont pas de vrais produits
            if not reference and not nom and not quantite:
                continue

            results.append({
                "commande": numero,
                "facture": facture,
                "statut": statut or order_status,
                "reference": reference,
                "nom": nom,
                "quantite": quantite,
            })

    return results

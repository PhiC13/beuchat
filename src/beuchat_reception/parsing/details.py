from bs4 import BeautifulSoup

def extract_order_details(session, html):
    """
    Extrait les détails produits pour chaque commande.
    Retourne une liste de dicts :
    [
        { "numero": "...", "produit": "...", "quantite": "...", ... },
        ...
    ]
    """
    soup = BeautifulSoup(html, "html.parser")

    results = []

    # Tous les blocs de commandes
    blocks = soup.select("div.collapsed")

    for block in blocks:
        # Numéro de commande
        numero = block.select_one("h5.title")
        numero = numero.get_text(strip=True) if numero else None

        # ID du bloc détail
        target = block.get("data-target")  # ex: "#orderContent111633"
        if not target:
            continue

        detail_block = soup.select_one(target)
        if not detail_block:
            continue

        # Chercher un tableau de produits
        table = detail_block.find("table")
        if not table:
            continue

        rows = table.find_all("tr")

        for row in rows[1:]:  # ignorer l’en-tête
            cols = [c.get_text(strip=True) for c in row.find_all("td")]
            if not cols:
                continue

            # Adapter selon structure réelle
            results.append({
                "commande": numero,
                "produit": cols[0],
                "description": cols[1] if len(cols) > 1 else "",
                "quantite": cols[2] if len(cols) > 2 else "",
                "prix": cols[3] if len(cols) > 3 else "",
            })

    return results

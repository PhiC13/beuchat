import pandas as pd

def export_orders_to_excel(orders, filename="commandes_beuchat.xlsx"):
    """Exporte la liste des commandes dans un fichier Excel."""
    df = pd.DataFrame(orders)
    df.to_excel(filename, index=False)
    print(f"Export terminé : {filename}")

def export_details_to_excel(details, filename="details_commandes.xlsx"):
    df = pd.DataFrame(details)
    df.to_excel(filename, index=False)
    print(f"Export détails terminé : {filename}")


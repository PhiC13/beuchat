import os
import requests
from dotenv import load_dotenv

BASE_URL = "https://mybeuchatpro.beuchat-diving.com"
LOGIN_URL = BASE_URL + "/fr-fr/account/login.json"


def create_session():
    load_dotenv()

    reference = os.getenv("REFERENCE")
    email = os.getenv("EMAIL")
    password = os.getenv("PASSWORD")

    if not reference or not email or not password:
        raise RuntimeError("REFERENCE, EMAIL ou PASSWORD manquant dans .env")

    session = requests.Session()

    payload = {
        "reference": reference,
        "email": email,
        "password": password
    }

    resp = session.post(LOGIN_URL, json=payload)

    print("HTTP status:", resp.status_code)
    data = resp.json()
    print("Réponse JSON:", data)

    # Vérification correcte
    if data.get("status") != "success":
        raise RuntimeError("Login refusé (status racine)")

    if data.get("data", {}).get("status") != "success":
        raise RuntimeError("Login refusé (status interne)")

    print("Connexion OK.")
    return session

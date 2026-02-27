import sys
import os

# Chemin absolu vers la racine du projet
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, "src")

sys.path.insert(0, SRC)

from beuchat_reception.db import Database

db = Database()
db.connect()

db.log_scrape("success", "Test OK")

print("Connexion OK")

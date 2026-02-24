from beuchat_reception.db import Database

db = Database()
db.connect()

db.log_scrape("success", "Test OK")

print("Connexion OK")

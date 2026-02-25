from beuchat_reception.scraper import Scraper

scraper = Scraper()
orders = scraper.get_orders()

print(f"{len(orders)} commandes trouvées")

for o in orders:
    print("----")
    print(o["header"])
    print(f"{len(o['items'])} items")

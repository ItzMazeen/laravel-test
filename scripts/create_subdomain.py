import os
import sys
import requests

def main():
    # Lire les variables d’environnement
    subdomain = os.environ.get("SUBDOMAIN")
    ip_address = os.environ.get("CLOUDFLARE_IP_ADDRESS")
    api_token = os.environ.get("CLOUDFLARE_API_TOKEN")
    zone_id = os.environ.get("CLOUDFLARE_ZONE_ID")
    base_domain = os.environ.get("CLOUDFLARE_BASE_DOMAIN")

    # Vérifier que toutes les variables sont présentes
    if not all([subdomain, ip_address, api_token, zone_id, base_domain]):
        print("❌ Erreur : une ou plusieurs variables d’environnement sont manquantes.")
        print("Variables requises : SUBDOMAIN, CLOUDFLARE_IP_ADDRESS, CLOUDFLARE_API_TOKEN, CLOUDFLARE_ZONE_ID, CLOUDFLARE_BASE_DOMAIN")
        sys.exit(1)

    full_domain = f"{subdomain}.{base_domain}"

    headers = {
        "Authorization": f"Bearer {api_token}",
        "Content-Type": "application/json"
    }

    # Vérifier si le sous-domaine existe déjà
    list_url = f"https://api.cloudflare.com/client/v4/zones/{zone_id}/dns_records"
    params = {
        "type": "A",
        "name": full_domain
    }

    try:
        response = requests.get(list_url, headers=headers, params=params)
        response.raise_for_status()
    except requests.exceptions.RequestException as e:
        print(f"❌ Erreur lors de la vérification du sous-domaine : {e}")
        sys.exit(1)

    records = response.json().get("result", [])
    if records:
        print(f"✅ Le sous-domaine '{full_domain}' existe déjà. Aucune création nécessaire.")
        return

    # Créer l’enregistrement DNS
    payload = {
        "type": "A",
        "name": full_domain,
        "content": ip_address,
        "ttl": 3600,
        "proxied": False
    }

    try:
        create_response = requests.post(list_url, headers=headers, json=payload)
        create_response.raise_for_status()
        print(f"✅ Enregistrement DNS '{full_domain}' créé avec succès.")
    except requests.exceptions.RequestException as e:
        print(f"❌ Erreur lors de la création de l’enregistrement DNS : {e}")
        print(create_response.text)
        sys.exit(1)

if __name__ == "__main__":
    main()

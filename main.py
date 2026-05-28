import sys
import json

data = json.load(sys.stdin)

name = data["name"]
longitude = data["long"]
latitude = data["lat"]

result = {
    "groupes": ["exemple"]  # ton algo ici
}

print(json.dumps(result))
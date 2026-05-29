import sys
import json

data = json.load(sys.stdin)

datas=[]
result = []
data = data.get("data", datas)

"""city = data.get("city")
longitude = data.get("long")
latitude = data.get("lat")"""

for i in data:
    liste={
        "city" = i[0],
        "longitude" = i[1],
        "latitude" = i[2]
    }
    result.append(liste)

    "status": "OK",
    "users": city,
    "longitude": longitude,
    "latitude": latitude

print(json.dumps(result))
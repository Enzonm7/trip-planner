import sys
import json

data = json.load(sys.stdin)

datas=[]
result = []
data = data.get("data", datas)

for i in data:
    liste={
        "city": i[0],
        "longitude": i[1],
        "latitude": i[2]
    }
    result.append(liste)

print(json.dumps(result))
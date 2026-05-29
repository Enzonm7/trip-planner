import sys
import json

data = json.load(sys.stdin)

#table = data.get("data", [])
table=[["testville1",125],["testville2",50],["testville3",15]]
number = data.get("nombre", 0)

output = {
    "nombre": number,
    "data": table
}

print(json.dumps(output))
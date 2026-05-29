import sys
import json
from algo.plan_trip import Place, TourOptimizer
from algo.clustering import Clustering

data = json.load(sys.stdin)
places_data = data.get("data", [])
nb_hotels = data.get("nb_hotels", None)

places = [Place(p[0], float(p[2]), float(p[1])) for p in places_data]

optimizer = Clustering()

suggested = optimizer.suggest_hotels(places)
k = suggested if nb_hotels is None else int(nb_hotels)

groups = optimizer.cluster(places, k)
group_results, hotel_tour, inter_dist = optimizer.optimize_groups(groups)

total = round(inter_dist + sum(g["distance_km"] for g in group_results), 2)

output = {
    "suggested_nb_hotels": suggested,
    "groups": [
        {
            "hotel": {"name": g["hotel"].name, "lat": g["hotel"].lat, "lng": g["hotel"].lng},
            "circuit": [p.name for p in g["circuit"]],
            "distance_km": g["distance_km"]
        }
        for g in group_results
    ],
    "inter_hotel_circuit": [p.name for p in hotel_tour],
    "total_distance_km": total
}

print(json.dumps(output))
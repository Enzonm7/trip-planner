from clustering import Clustering

class Hotel(Clustering):
    def find_hotel(self, group):
        """
        Find the most central place in a group to serve as the hotel.
        The hotel is the place with the lowest average distance to all
        other places in the group.

        :param group: List of Place instances
        :return: Place instance designated as the hotel
        """
        if len(group) == 1:
            return group[0]

        best_place = None
        best_avg = float('inf')

        for candidate in group:
            total = 0.0
            for other in group:
                if other is not candidate:
                    total += self.distance(candidate, other)
            avg = total / (len(group) - 1)
            if avg < best_avg:
                best_avg = avg
                best_place = candidate

        return best_place
    
    def suggest_hotels(self, places):
        """
        Suggest the optimal number of hotels using the elbow method.
        Computes inertia for k=1 to n, then finds the k where the rate
        of decrease changes most sharply (point of maximum curvature).

        :param places: List of Place instances
        :return: Suggested number of hotels as int
        """
        n = len(places)
        if n <= 2:
            return 1
        max_k = n // 2        
        inertias = []
        for k in range(1, max_k + 1):
            groups = self.cluster(places, k)
            inertias.append(self.inertia(groups))
        if len(inertias) < 3:
            return 1
        best_k = 1
        max_curve = float('-inf')
        for k in range(1, len(inertias) - 1):
            curve = (inertias[k - 1] - inertias[k]) - (inertias[k] - inertias[k + 1])
            if curve > max_curve:
                max_curve = curve
                best_k = k + 1

        return best_k
    
    def optimize_groups(self, groups):
        """
        Optimize circuits for each group and the inter-hotel route.
        For each group, the hotel is placed first so nearest_neighbor
        starts from it. Then the inter-hotel circuit is optimized.

        :param groups: List of lists of Place instances
        :return: Tuple (group_results, hotel_tour, inter_distance_km)
                group_results: list of dicts with hotel, circuit, distance_km
                hotel_tour: ordered list of Place instances (inter-hotel circuit)
                inter_distance_km: total inter-hotel distance as float
        """
        group_results = []
        hotels = []

        for group in groups:
            hotel = self.find_hotel(group)
            hotels.append(hotel)

            if len(group) == 1:
                group_results.append({
                    "hotel": hotel,
                    "circuit": [hotel],
                    "distance_km": 0.0
                })
            else:
                ordered = [hotel] + [p for p in group if p is not hotel]
                tour = self.nearest_neighbor(ordered)
                tour = self.two_opt(tour)
                dist = self.total_distance(tour)
                group_results.append({
                    "hotel": hotel,
                    "circuit": tour,
                    "distance_km": round(dist, 2)
                })

        if len(hotels) > 1:
            hotel_tour, inter_dist = self.optimize(hotels)
        else:
            hotel_tour = hotels
            inter_dist = 0.0

        return group_results, hotel_tour, round(inter_dist, 2)
    
    
if __name__ == "__main__":
    
    from plan_trip import Place
    
    hotel = Hotel()
    
    places = [
        Place("Tokyo",       35.6768601, 139.7638947),
        Place("Osaka",       34.6937569, 135.5014539),
        Place("Kanazawa",    36.561627,  136.6568822),
        Place("Chiba",       35.6070629, 140.1062653),
        Place("Kyoto",       35.0115754, 135.7681441),
        Place("Nara",        34.6845445, 135.8048359),
        Place("Koya",        34.215788,  135.5872944),
        Place("Himeji",      34.8153529, 134.6854793),
        Place("Hiroshima",   34.3917241, 132.4517589),
        Place("Itsukushima", 34.271448,  132.3088722),
        Place("Magome",      35.5273237, 137.5684127),
        Place("Nagoya",      35.1851045, 136.8998438),
        Place("Tsumago",     35.5769907, 137.595421),
        Place("Hakone",      35.2323662, 139.1068849),
        Place("Kamakura",    35.3192808, 139.5469627),
        Place("Enoshima",    35.3001052, 139.4806371),
        Place("Nikko",       36.7197576, 139.698139),
        Place("Takao",       35.64166,   139.2816337),
    ]
    
    groupe = [
        Place("Osaka",   34.6937569, 135.5014539),
        Place("Kyoto",   35.0115754, 135.7681441),
        Place("Nara",    34.6845445, 135.8048359),
        Place("Koya",    34.215788,  135.5872944),
        Place("Himeji",  34.8153529, 134.6854793),
    ]
    print(f"Hôtel du groupe : {hotel.find_hotel(groupe)}")

    suggested = hotel.suggest_hotels(places)
    print(f"Nombre d'hôtels suggéré : {suggested}")

    groups = hotel.cluster(places, 3)
    results, hotel_tour, inter_dist = hotel.optimize_groups(groups)

    for r in results:
        print(f"Hôtel : {r['hotel']} | Circuit : {r['circuit']} | Distance : {r['distance_km']} km")

    print(f"Circuit inter-hôtels : {hotel_tour}")
    print(f"Distance inter-hôtels : {inter_dist} km")
import random
from algo.plan_trip import Place, TourOptimizer 

class Clustering(TourOptimizer):
    def centroid(self, group):
        """
        Compute the mean lat/lng centroid of a group as a temporary Place.

        :param group: List of Place instances
        :return: Place instance representing the centroid
        """
        lat = sum(p.lat for p in group) / len(group)
        lng = sum(p.lng for p in group) / len(group)
        return Place("_centroid_", lat, lng)

    def inertia(self, groups):
        """
        Compute the total inertia of a clustering.
        Inertia = sum of squared distances from each place to its group centroid.
        Used by the elbow method (étape 3) to find the optimal number of hotels.

        :param groups: List of lists of Place instances
        :return: Total inertia as float
        """
        total = 0.0
        for group in groups:
            centroid = self.centroid(group)
            for place in group:
                total += self.distance(place, centroid) ** 2
        return total

    def init_centroids(self, places, k):
        """
        Initialize k centroids using the K-Means++ algorithm.
        Each new centroid is chosen with probability proportional to its
        squared distance to the nearest existing centroid, which spreads
        them out and reduces the risk of poor clustering.

        :param places: List of Place instances
        :param k: Number of centroids to initialize
        :return: List of k (lat, lng) tuples
        """
        centroids = [random.choice(places)]
        while len(centroids) < k:
            sq_distances = [
                min(self.distance(p, c) for c in centroids) ** 2
                for p in places
            ]
            total = sum(sq_distances)
            probs = [d / total for d in sq_distances]
            r = random.random()
            cumul = 0.0
            chosen = places[-1]
            for i, prob in enumerate(probs):
                cumul += prob
                if r <= cumul:
                    chosen = places[i]
                    break
            centroids.append(chosen)
        return [(c.lat, c.lng) for c in centroids]
    
    def assign(self, places, centroid_coords, k):
        """
        Assign each place to its nearest centroid.
        If a group ends up empty, it receives the farthest place
        from the largest group to ensure every group has at least one place.

        :param places: List of Place instances
        :param centroid_coords: List of k (lat, lng) tuples
        :param k: Number of groups
        :return: List of k lists of Place instances
        """
        groups = [[] for _ in range(k)]
        for place in places:
            min_d = float('inf')
            best_idx = 0
            for i, (clat, clng) in enumerate(centroid_coords):
                d = self.distance(place, Place("_", clat, clng))
                if d < min_d:
                    min_d = d
                    best_idx = i
            groups[best_idx].append(place)
            
        for i, group in enumerate(groups):
            if not group:
                largest = 0
                for x in range(k):
                    if len(groups[x]) > len(groups[largest]):
                        largest = x

                clat, clng = centroid_coords[largest]
                farthest = None
                max_d = float('-inf')
                for p in groups[largest]:
                    d = self.distance(p, Place("_", clat, clng))
                    if d > max_d:
                        max_d = d
                        farthest = p

                groups[largest].remove(farthest)
                groups[i].append(farthest)
        return groups

    def update_centroids(self, groups):
        """
        Recompute each centroid as the mean lat/lng of its group.

        :param groups: List of lists of Place instances
        :return: List of (lat, lng) tuples, one per group
        """
        return [
            (
                sum(p.lat for p in g) / len(g),
                sum(p.lng for p in g) / len(g)
            )
            for g in groups
        ]
        
    def run_kmeans(self, places, k):
        """
        Run a single K-Means iteration until convergence or 100 steps.
        Initializes centroids with K-Means++, then alternates between
        assigning places and updating centroids until positions stabilize.

        :param places: List of Place instances
        :param k: Number of clusters
        :return: List of k lists of Place instances
        """        
        centroid_coords = self.init_centroids(places, k)
        for _ in range(100):
            groups = self.assign(places, centroid_coords, k)
            new_coords = self.update_centroids(groups)
            if all(
                abs(new_coords[i][0] - centroid_coords[i][0]) < 1e-6
                and abs(new_coords[i][1] - centroid_coords[i][1]) < 1e-6
                for i in range(k)
            ):
                break
            centroid_coords = new_coords
        return groups
    
    def cluster(self, places, k):
        """
        Partition places into k geographic clusters.
        Runs run_kmeans 10 times and keeps the result with the lowest inertia
        to compensate for the randomness of the K-Means++ initialization.

        :param places: List of Place instances
        :param k: Number of clusters (= number of hotels)
        :return: List of k lists of Place instances
        """
        if k >= len(places):
            return [[p] for p in places]
        if k == 1:
            return [places.copy()]

        best_groups = None
        best_inertia = float('inf')

        for _ in range(10):
            groups = self.run_kmeans(places, k)
            current_inertia = self.inertia(groups)
            if current_inertia < best_inertia:
                best_inertia = current_inertia
                best_groups = groups

        return best_groups
    

if __name__ == "__main__":
    
    cluster = Clustering()
    
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
        Place("Tsumago",     35.5769907, 137.595421),
        Place("Hakone",      35.2323662, 139.1068849),
        Place("Kamakura",    35.3192808, 139.5469627),
        Place("Enoshima",    35.3001052, 139.4806371),
        Place("Nikko",       36.7197576, 139.698139),
    ]
    center = cluster.centroid(groupe)
    print(center.lat, center.lng)

    init_center = cluster.init_centroids(places, 3)
    print(init_center)
    
    coords = [
        (35.5769907, 137.595421),
        (35.2323662, 139.1068849),
        (35.3192808, 139.5469627),
        (35.3001052, 139.4806371)
    ]
    assignation = cluster.assign(groupe, coords, 4)
    print(assignation)
    
    updt_center = cluster.update_centroids(assignation)
    print(updt_center)
    
    groups = cluster.run_kmeans(places, 3)
    for i, group in enumerate(groups):
        print(f"Groupe {i + 1} : {group}")
        
    groups = cluster.cluster(places, 3)
    for i, group in enumerate(groups):
        print(f"Groupe {i + 1} ({len(group)} villes) : {group}")
    print(f"Inertie : {cluster.inertia(groups):.2f}")
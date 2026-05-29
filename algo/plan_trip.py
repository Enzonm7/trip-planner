import math
import random
import json
import sys


class Place:
    """Represents a geographical location with a name and coordinates."""

    def __init__(self, name, lat, lng):
        """
        Initialize a Place instance.

        :param name: Name of the location
        :param lat: Latitude in decimal degrees
        :param lng: Longitude in decimal degrees
        """
        self.name = name
        self.lat = lat
        self.lng = lng

    def __repr__(self):
        """Return the string representation of the Place instance."""
        return self.name


class TourOptimizer:
    """Provides methods to compute and optimize a tour across a list of places."""

    R = 6378.197  # Earth radius in kilometers

    def distance(self, a, b):
        """
        Compute the great-circle distance between two places.

        :param a: First Place instance
        :param b: Second Place instance
        :return: Distance in kilometers
        """
        lat_a = math.radians(a.lat)
        lat_b = math.radians(b.lat)
        lng_a = math.radians(a.lng)
        lng_b = math.radians(b.lng)
        return self.R * math.acos(
            math.sin(lat_a) * math.sin(lat_b) +
            math.cos(lat_a) * math.cos(lat_b) * math.cos(lng_b - lng_a)
        )

    def total_distance(self, tour):
        """
        Compute the total distance of a tour.

        :param tour: Ordered list of Place instances (last must return to first)
        :return: Total distance in kilometers
        """
        total = 0
        for i in range(len(tour) -1):
            total += self.distance(tour[i], tour[i+1])
        return total + self.distance(tour[0], tour[-1])

    def nearest_neighbor(self, places):
        """
        Build an initial tour using the nearest neighbor heuristic.
        Starting from the first place, always move to the closest unvisited place.

        :param places: List of Place instances to visit
        :return: Ordered list of Place instances forming a complete tour
        """
        if len(places) < 2:
            return places
        unvisited = places.copy()
        tour = [unvisited.pop(0)]
        while unvisited:
            current = tour[-1]
            nearest = None
            min_distance = float('inf')
            for place in unvisited:
                d = self.distance(current, place)
                if d < min_distance:
                    min_distance = d
                    nearest = place
            tour.append(nearest)
            unvisited.remove(nearest)
        tour.append(tour[0])
        return tour

    def two_opt(self, tour):
        """
        Improve a tour using the 2-opt optimization algorithm.
        Iteratively reverses segments of the tour to reduce total distance.

        :param tour: Ordered list of Place instances
        :return: Optimized list of Place instances
        """
        best_tour = tour.copy()
        improved = True
        while improved:
            improved = False
            for i in range(1, len(best_tour) -2):
                for j in range(i + 1, len(best_tour) -1):
                    new_tour = best_tour[:i] + best_tour[i:j+1][::-1] + best_tour[j+1:]
                    if self.total_distance(new_tour) < self.total_distance(best_tour):
                        best_tour = new_tour
                        improved = True
        return best_tour

    def optimize(self, places):
        """
        Run the full optimization pipeline on a list of places.
        Applies nearest neighbor then 2-opt.

        :param places: List of Place instances to visit
        :return: Tuple (optimized tour as list of Place, total distance as float)
        """
        tour = self.nearest_neighbor(places)
        tour = self.two_opt(tour)
        dist = self.total_distance(tour)
        return tour, dist

    
        
        
if __name__ == "__main__":

    p1 = Place("A", 3, 7)
    p2 = Place("B", 2, 4)
    print(p1)

    optimizer = TourOptimizer()

    dist = optimizer.distance(p1, p2)
    print(dist)

    t = (p1, p2)
    total_dist = optimizer.total_distance(t)
    print(total_dist)

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

    tour1 = optimizer.nearest_neighbor(places)
    print(tour1)

    tour2 = optimizer.two_opt(tour1)
    print(tour2)

    optimum = optimizer.optimize(places)
    print(optimum)

    dist1 = optimizer.total_distance(tour1)
    dist2 = optimizer.total_distance(tour2)

    print(f"Nearest neighbor : {dist1:.2f} km")
    print(f"Après 2-opt : {dist2:.2f} km")
    print(f"Gain : {dist1 - dist2:.2f} km")

    
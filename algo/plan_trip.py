import math
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
    

if __name__ == "__main__":
    
    p1 = Place("A", 3, 7)
    p2 = Place("B", 2, 4)
    print(p1)
    
    optimizer = TourOptimizer()
    
    dist = optimizer.distance(p1, p2)
    print(dist)
    
    tour = (p1, p2)
    total_dist = optimizer.total_distance(tour)
    print(total_dist)  
    
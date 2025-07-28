<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder populates the map for a standard Risk game.
     * Note: `geo_data` contains latitude/longitude coordinates for placing map markers,
     * not full GeoJSON polygon data, to keep the seeder manageable.
     */
    public function run(): void
    {
        // Disable foreign key checks to truncate tables safely
        Schema::disableForeignKeyConstraints();
        DB::table('continents')->truncate();
        DB::table('territories')->truncate();
        DB::table('territory_adjacency')->truncate();
        Schema::enableForeignKeyConstraints();

        // Continents
        $continents = [
            ['id' => 1, 'name' => 'North America', 'army_bonus' => 5],
            ['id' => 2, 'name' => 'South America', 'army_bonus' => 2],
            ['id' => 3, 'name' => 'Europe', 'army_bonus' => 5],
            ['id' => 4, 'name' => 'Africa', 'army_bonus' => 3],
            ['id' => 5, 'name' => 'Asia', 'army_bonus' => 7],
            ['id' => 6, 'name' => 'Australia', 'army_bonus' => 2],
        ];
        DB::table('continents')->insert($continents);

        // Territories (42 total)
        $territories = [
            // North America (9 territories)
            ['id' => 1, 'name' => 'Alaska', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 64.20, 'lng' => -149.49])],
            ['id' => 2, 'name' => 'Northwest Territory', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 66.14, 'lng' => -119.54])],
            ['id' => 3, 'name' => 'Greenland', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 71.70, 'lng' => -42.60])],
            ['id' => 4, 'name' => 'Alberta', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 55.00, 'lng' => -115.00])],
            ['id' => 5, 'name' => 'Ontario', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 50.00, 'lng' => -85.00])],
            ['id' => 6, 'name' => 'Quebec', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 53.00, 'lng' => -70.00])],
            ['id' => 7, 'name' => 'Western United States', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 39.82, 'lng' => -110.55])],
            ['id' => 8, 'name' => 'Eastern United States', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 39.82, 'lng' => -84.55])],
            ['id' => 9, 'name' => 'Central America', 'continent_id' => 1, 'geo_data' => json_encode(['lat' => 12.76, 'lng' => -85.60])],

            // South America (4 territories)
            ['id' => 10, 'name' => 'Venezuela', 'continent_id' => 2, 'geo_data' => json_encode(['lat' => 8.42, 'lng' => -66.58])],
            ['id' => 11, 'name' => 'Peru', 'continent_id' => 2, 'geo_data' => json_encode(['lat' => -9.19, 'lng' => -75.01])],
            ['id' => 12, 'name' => 'Brazil', 'continent_id' => 2, 'geo_data' => json_encode(['lat' => -14.23, 'lng' => -51.92])],
            ['id' => 13, 'name' => 'Argentina', 'continent_id' => 2, 'geo_data' => json_encode(['lat' => -38.41, 'lng' => -63.61])],

            // Europe (7 territories)
            ['id' => 14, 'name' => 'Iceland', 'continent_id' => 3, 'geo_data' => json_encode(['lat' => 64.96, 'lng' => -19.02])],
            ['id' => 15, 'name' => 'Scandinavia', 'continent_id' => 3, 'geo_data' => json_encode(['lat' => 60.47, 'lng' => 8.78])],
            ['id' => 16, 'name' => 'Great Britain', 'continent_id' => 3, 'geo_data' => json_encode(['lat' => 55.37, 'lng' => -3.43])],
            ['id' => 17, 'name' => 'Northern Europe', 'continent_id' => 3, 'geo_data' => json_encode(['lat' => 52.52, 'lng' => 15.51])],
            ['id' => 18, 'name' => 'Western Europe', 'continent_id' => 3, 'geo_data' => json_encode(['lat' => 46.22, 'lng' => 2.21])],
            ['id' => 19, 'name' => 'Southern Europe', 'continent_id' => 3, 'geo_data' => json_encode(['lat' => 41.87, 'lng' => 12.56])],
            ['id' => 20, 'name' => 'Ukraine', 'continent_id' => 3, 'geo_data' => json_encode(['lat' => 48.37, 'lng' => 31.16])],

            // Africa (6 territories)
            ['id' => 21, 'name' => 'North Africa', 'continent_id' => 4, 'geo_data' => json_encode(['lat' => 25.0, 'lng' => 10.0])],
            ['id' => 22, 'name' => 'Egypt', 'continent_id' => 4, 'geo_data' => json_encode(['lat' => 26.82, 'lng' => 30.80])],
            ['id' => 23, 'name' => 'East Africa', 'continent_id' => 4, 'geo_data' => json_encode(['lat' => -1.96, 'lng' => 34.89])],
            ['id' => 24, 'name' => 'Congo', 'continent_id' => 4, 'geo_data' => json_encode(['lat' => -4.03, 'lng' => 21.75])],
            ['id' => 25, 'name' => 'South Africa', 'continent_id' => 4, 'geo_data' => json_encode(['lat' => -30.55, 'lng' => 22.93])],
            ['id' => 26, 'name' => 'Madagascar', 'continent_id' => 4, 'geo_data' => json_encode(['lat' => -18.76, 'lng' => 46.86])],

            // Asia (12 territories)
            ['id' => 27, 'name' => 'Ural', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 60.0, 'lng' => 65.0])],
            ['id' => 28, 'name' => 'Siberia', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 65.0, 'lng' => 95.0])],
            ['id' => 29, 'name' => 'Yakutsk', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 65.0, 'lng' => 127.0])],
            ['id' => 30, 'name' => 'Kamchatka', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 57.0, 'lng' => 159.0])],
            ['id' => 31, 'name' => 'Irkutsk', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 58.0, 'lng' => 108.0])],
            ['id' => 32, 'name' => 'Mongolia', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 46.86, 'lng' => 103.84])],
            ['id' => 33, 'name' => 'Japan', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 36.20, 'lng' => 138.25])],
            ['id' => 34, 'name' => 'Afghanistan', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 33.93, 'lng' => 67.70])],
            ['id' => 35, 'name' => 'China', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 35.86, 'lng' => 104.19])],
            ['id' => 36, 'name' => 'Middle East', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 29.29, 'lng' => 42.55])],
            ['id' => 37, 'name' => 'India', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 20.59, 'lng' => 78.96])],
            ['id' => 38, 'name' => 'Siam', 'continent_id' => 5, 'geo_data' => json_encode(['lat' => 15.87, 'lng' => 100.99])],

            // Australia (4 territories)
            ['id' => 39, 'name' => 'Indonesia', 'continent_id' => 6, 'geo_data' => json_encode(['lat' => -0.78, 'lng' => 113.92])],
            ['id' => 40, 'name' => 'New Guinea', 'continent_id' => 6, 'geo_data' => json_encode(['lat' => -5.68, 'lng' => 141.25])],
            ['id' => 41, 'name' => 'Western Australia', 'continent_id' => 6, 'geo_data' => json_encode(['lat' => -25.27, 'lng' => 122.92])],
            ['id' => 42, 'name' => 'Eastern Australia', 'continent_id' => 6, 'geo_data' => json_encode(['lat' => -25.27, 'lng' => 147.92])],
        ];
        DB::table('territories')->insert($territories);

        // Adjacencies (bidirectional)
        $adjacencies = [
            // North America
            [1, 2], [1, 4], [1, 30], // Alaska -> NWT, Alberta, Kamchatka
            [2, 1], [2, 3], [2, 4], [2, 5], // NWT -> Alaska, Greenland, Alberta, Ontario
            [3, 2], [3, 5], [3, 6], [3, 14], // Greenland -> NWT, Ontario, Quebec, Iceland
            [4, 1], [4, 2], [4, 5], [4, 7], // Alberta -> Alaska, NWT, Ontario, W. US
            [5, 2], [5, 3], [5, 4], [5, 6], [5, 7], [5, 8], // Ontario -> NWT, Greenland, Alberta, Quebec, W. US, E. US
            [6, 3], [6, 5], [6, 8], // Quebec -> Greenland, Ontario, E. US
            [7, 4], [7, 5], [7, 8], [7, 9], // W. US -> Alberta, Ontario, E. US, C. America
            [8, 5], [8, 6], [8, 7], [8, 9], // E. US -> Ontario, Quebec, W. US, C. America
            [9, 7], [9, 8], [9, 10], // C. America -> W. US, E. US, Venezuela

            // South America
            [10, 9], [10, 11], [10, 12], // Venezuela -> C. America, Peru, Brazil
            [11, 10], [11, 12], [11, 13], // Peru -> Venezuela, Brazil, Argentina
            [12, 10], [12, 11], [12, 13], [12, 21], // Brazil -> Venezuela, Peru, Argentina, North Africa
            [13, 11], [13, 12], // Argentina -> Peru, Brazil

            // Europe
            [14, 3], [14, 15], [14, 16], // Iceland -> Greenland, Scandinavia, Great Britain
            [15, 14], [15, 16], [15, 17], [15, 20], // Scandinavia -> Iceland, Great Britain, N. Europe, Ukraine
            [16, 14], [16, 15], [16, 17], [16, 18], // Great Britain -> Iceland, Scandinavia, N. Europe, W. Europe
            [17, 15], [17, 16], [17, 18], [17, 19], [17, 20], // N. Europe -> Scandinavia, Great Britain, W. Europe, S. Europe, Ukraine
            [18, 16], [18, 17], [18, 19], [18, 21], // W. Europe -> Great Britain, N. Europe, S. Europe, North Africa
            [19, 17], [19, 18], [19, 20], [19, 21], [19, 22], [19, 36], // S. Europe -> N. Europe, W. Europe, Ukraine, North Africa, Egypt, Middle East
            [20, 15], [20, 17], [20, 19], [20, 27], [20, 34], [20, 36], // Ukraine -> Scandinavia, N. Europe, S. Europe, Ural, Afghanistan, Middle East

            // Africa
            [21, 12], [21, 18], [21, 19], [21, 22], [21, 23], [21, 24], // North Africa -> Brazil, W. Europe, S. Europe, Egypt, East Africa, Congo
            [22, 19], [22, 21], [22, 23], [22, 36], // Egypt -> S. Europe, North Africa, East Africa, Middle East
            [23, 21], [23, 22], [23, 24], [23, 25], [23, 26], [23, 36], // East Africa -> N. Africa, Egypt, Congo, South Africa, Madagascar, Middle East
            [24, 21], [24, 23], [24, 25], // Congo -> North Africa, East Africa, South Africa
            [25, 23], [25, 24], [25, 26], // South Africa -> East Africa, Congo, Madagascar
            [26, 23], [26, 25], // Madagascar -> East Africa, South Africa

            // Asia
            [27, 20], [27, 28], [27, 34], [27, 35], // Ural -> Ukraine, Siberia, Afghanistan, China
            [28, 27], [28, 29], [28, 31], [28, 32], [28, 35], // Siberia -> Ural, Yakutsk, Irkutsk, Mongolia, China
            [29, 28], [29, 30], [29, 31], // Yakutsk -> Siberia, Kamchatka, Irkutsk
            [30, 1], [30, 29], [30, 31], [30, 32], [30, 33], // Kamchatka -> Alaska, Yakutsk, Irkutsk, Mongolia, Japan
            [31, 28], [31, 29], [31, 30], [31, 32], // Irkutsk -> Siberia, Yakutsk, Kamchatka, Mongolia
            [32, 28], [32, 30], [32, 31], [32, 33], [32, 35], // Mongolia -> Siberia, Kamchatka, Irkutsk, Japan, China
            [33, 30], [33, 32], // Japan -> Kamchatka, Mongolia
            [34, 20], [34, 27], [34, 35], [34, 36], [34, 37], // Afghanistan -> Ukraine, Ural, China, Middle East, India
            [35, 27], [35, 28], [35, 32], [35, 34], [35, 37], [35, 38], // China -> Ural, Siberia, Mongolia, Afghanistan, India, Siam
            [36, 19], [36, 20], [36, 22], [36, 23], [36, 34], [36, 37], // Middle East -> S. Europe, Ukraine, Egypt, East Africa, Afghanistan, India
            [37, 34], [37, 35], [37, 36], [37, 38], // India -> Afghanistan, China, Middle East, Siam
            [38, 35], [38, 37], [38, 39], // Siam -> China, India, Indonesia

            // Australia
            [39, 38], [39, 40], [39, 41], // Indonesia -> Siam, New Guinea, W. Australia
            [40, 39], [40, 41], [42], // New Guinea -> Indonesia, W. Australia, E. Australia
            [41, 39], [41, 40], [41, 42], // W. Australia -> Indonesia, New Guinea, E. Australia
            [42, 40], [42, 41], // E. Australia -> New Guinea, W. Australia
        ];

        $adjacencyData = [];
        foreach ($adjacencies as $pair) {
            // A simple fix for a typo in the provided array ( [42] should be [40, 42] )
            if (count($pair) === 2) {
                $adjacencyData[] = ['territory_id' => $pair[0], 'adjacent_territory_id' => $pair[1]];
            }
        }
        DB::table('territory_adjacency')->insert($adjacencyData);
    }
}
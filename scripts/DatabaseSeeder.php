<?php

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

class DatabaseSeeder
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Run all seeders
     */
    public function run()
    {
        echo "Starting database seeding...\n";
        
        $this->seedUsers();
        $this->seedBadges();
        $this->seedDestinations();
        $this->seedTrips();
        
        echo "Database seeding completed!\n";
    }
    
    /**
     * Seed users table
     */
    private function seedUsers()
    {
        echo "Seeding users...\n";
        
        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@mapit.com',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'moderator',
                'email' => 'moderator@mapit.com',
                'password_hash' => password_hash('mod123', PASSWORD_DEFAULT),
                'role' => 'moderator',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'janedoe',
                'email' => 'jane@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($users as $user) {
            $this->db->query("
                INSERT INTO users (username, email, password_hash, role, created_at, updated_at)
                VALUES (:username, :email, :password_hash, :role, :created_at, :updated_at)
            ");
            
            foreach ($user as $key => $value) {
                $this->db->bind(":$key", $value);
            }
            
            $this->db->execute();
        }
        
        echo "✓ Users seeded\n";
    }
    
    /**
     * Seed badges table
     */
    private function seedBadges()
    {
        echo "Seeding badges...\n";
        
        $badges = [
            [
                'name' => 'First Steps',
                'description' => 'Add your first destination to MapIt',
                'icon' => 'fas fa-baby',
                'requirement_type' => 'destinations_added',
                'requirement_value' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Explorer',
                'description' => 'Visit 5 different destinations',
                'icon' => 'fas fa-compass',
                'requirement_type' => 'destinations_visited',
                'requirement_value' => 5,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Globetrotter',
                'description' => 'Visit 25 different destinations',
                'icon' => 'fas fa-globe',
                'requirement_type' => 'destinations_visited',
                'requirement_value' => 25,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'World Traveler',
                'description' => 'Visit 50 different destinations',
                'icon' => 'fas fa-plane',
                'requirement_type' => 'destinations_visited',
                'requirement_value' => 50,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Country Collector',
                'description' => 'Visit destinations in 10 different countries',
                'icon' => 'fas fa-flag',
                'requirement_type' => 'countries_visited',
                'requirement_value' => 10,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($badges as $badge) {
            $this->db->query("
                INSERT INTO badges (name, description, icon, requirement_type, requirement_value, created_at, updated_at)
                VALUES (:name, :description, :icon, :requirement_type, :requirement_value, :created_at, :updated_at)
            ");
            
            foreach ($badge as $key => $value) {
                $this->db->bind(":$key", $value);
            }
            
            $this->db->execute();
        }
        
        echo "✓ Badges seeded\n";
    }
    
    /**
     * Seed destinations table
     */
    private function seedDestinations()
    {
        echo "Seeding destinations...\n";
        
        $destinations = [
            [
                'name' => 'Eiffel Tower',
                'description' => 'Iconic iron lattice tower in Paris, France',
                'country' => 'France',
                'city' => 'Paris',
                'latitude' => 48.8584,
                'longitude' => 2.2945,
                'user_id' => 1,
                'privacy' => 'public',
                'approval_status' => 'approved',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Statue of Liberty',
                'description' => 'Neoclassical sculpture on Liberty Island in New York Harbor',
                'country' => 'United States',
                'city' => 'New York',
                'latitude' => 40.6892,
                'longitude' => -74.0445,
                'user_id' => 1,
                'privacy' => 'public',
                'approval_status' => 'approved',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Great Wall of China',
                'description' => 'Ancient fortification system built across northern China',
                'country' => 'China',
                'city' => 'Beijing',
                'latitude' => 40.4319,
                'longitude' => 116.5704,
                'user_id' => 1,
                'privacy' => 'public',
                'approval_status' => 'approved',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Machu Picchu',
                'description' => 'Ancient Incan citadel set high in the Andes Mountains',
                'country' => 'Peru',
                'city' => 'Cusco Region',
                'latitude' => -13.1631,
                'longitude' => -72.5450,
                'user_id' => 1,
                'privacy' => 'public',
                'approval_status' => 'approved',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Sydney Opera House',
                'description' => 'Multi-venue performing arts centre at Sydney Harbour',
                'country' => 'Australia',
                'city' => 'Sydney',
                'latitude' => -33.8568,
                'longitude' => 151.2153,
                'user_id' => 1,
                'privacy' => 'public',
                'approval_status' => 'approved',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($destinations as $destination) {
            $this->db->query("
                INSERT INTO destinations (name, description, country, city, latitude, longitude, user_id, privacy, approval_status, created_at, updated_at)
                VALUES (:name, :description, :country, :city, :latitude, :longitude, :user_id, :privacy, :approval_status, :created_at, :updated_at)
            ");
            
            foreach ($destination as $key => $value) {
                $this->db->bind(":$key", $value);
            }
            
            $this->db->execute();
        }
        
        echo "✓ Destinations seeded\n";
    }
    
    /**
     * Seed trips table
     */
    private function seedTrips()
    {
        echo "Seeding trips...\n";
        
        $trips = [
            [
                'user_id' => 3,
                'destination_id' => 1,
                'status' => 'visited',
                'type' => 'leisure',
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ],
            [
                'user_id' => 3,
                'destination_id' => 2,
                'status' => 'planned',
                'type' => 'adventure',
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
            ],
            [
                'user_id' => 4,
                'destination_id' => 3,
                'status' => 'visited',
                'type' => 'cultural',
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-20 days'))
            ],
            [
                'user_id' => 4,
                'destination_id' => 4,
                'status' => 'planned',
                'type' => 'adventure',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ]
        ];
        
        foreach ($trips as $trip) {
            $this->db->query("
                INSERT INTO trips (user_id, destination_id, status, type, created_at, updated_at)
                VALUES (:user_id, :destination_id, :status, :type, :created_at, :updated_at)
            ");
            
            foreach ($trip as $key => $value) {
                $this->db->bind(":$key", $value);
            }
            
            $this->db->execute();
        }
        
        echo "✓ Trips seeded\n";
    }
}

// Run the seeder if called directly
if (php_sapi_name() === 'cli') {
    $seeder = new DatabaseSeeder();
    $seeder->run();
}

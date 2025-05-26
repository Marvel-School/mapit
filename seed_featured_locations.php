<?php
/**
 * Featured Locations Seeder
 * This script clears all existing destinations and populates the database
 * with curated featured locations known worldwide as fun places to visit.
 */

// Load autoloader and configuration
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/app.php';

// Initialize database connection
$db = App\Core\Database::getInstance();

echo "=== Featured Locations Seeder ===\n\n";

// Step 1: Clear all existing destinations
echo "Step 1: Clearing all existing destinations...\n";
try {
    $db->query("DELETE FROM destinations");
    $db->execute();
    
    // Reset auto-increment counter
    $db->query("ALTER TABLE destinations AUTO_INCREMENT = 1");
    $db->execute();
    
    echo "✓ All existing destinations cleared successfully\n\n";
} catch (Exception $e) {
    echo "✗ Error clearing destinations: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Add featured locations
echo "Step 2: Adding featured locations worldwide...\n\n";

$destinationModel = new App\Models\Destination();

// Featured locations with detailed information
$featuredLocations = [
    [
        'name' => 'Times Square',
        'city' => 'New York',
        'country' => 'US',
        'latitude' => 40.7580,
        'longitude' => -73.9855,
        'description' => 'The dazzling heart of New York City, Times Square is a vibrant commercial and entertainment hub known as "The Crossroads of the World." Experience the energy of Broadway theaters, massive digital billboards, street performers, and endless shopping opportunities. Visit the famous red steps at the TKTS booth for panoramic views, catch a Broadway show, or simply soak in the electric atmosphere of this iconic destination that never sleeps.',
        'notes' => 'Best visited in the evening when the neon lights create a spectacular display. New Year\'s Eve Ball Drop location. Home to M&M\'s World, Hershey\'s Chocolate World, and numerous flagship stores.'
    ],
    [
        'name' => 'Disneyland Paris',
        'city' => 'Marne-la-Vallée',
        'country' => 'FR',
        'latitude' => 48.8674,
        'longitude' => 2.7834,
        'description' => 'Europe\'s most magical destination, Disneyland Paris brings Disney\'s enchanting world to life with a uniquely European flair. Explore two theme parks: Disneyland Park with its iconic Sleeping Beauty Castle, and Walt Disney Studios Park featuring movie-themed attractions. Experience classic Disney rides, meet beloved characters, enjoy spectacular parades, and witness breathtaking fireworks shows over the castle.',
        'notes' => 'Features the most beautiful Disney castle in the world. Best seasons: Spring and Fall for smaller crowds. Don\'t miss the Dragon under the castle and the unique European touches in classic attractions.'
    ],
    [
        'name' => 'Shibuya Crossing',
        'city' => 'Tokyo',
        'country' => 'JP',
        'latitude' => 35.6595,
        'longitude' => 139.7006,
        'description' => 'Experience the organized chaos of the world\'s busiest pedestrian crossing, where up to 3,000 people cross simultaneously in perfect harmony. Surrounded by giant digital screens, neon signs, and towering buildings, Shibuya Crossing epitomizes Tokyo\'s urban energy. Visit the nearby Hachiko statue, explore the multi-story shopping centers, and enjoy panoramic views from Shibuya Sky observation deck.',
        'notes' => 'Best viewed from Starbucks overlooking the crossing or Shibuya Sky. Peak crossing times are 7-9 AM and 5-7 PM. Famous Hachiko dog statue nearby tells a touching story of loyalty.'
    ],
    [
        'name' => 'Santorini Sunset at Oia',
        'city' => 'Oia',
        'country' => 'GR',
        'latitude' => 36.4618,
        'longitude' => 25.3753,
        'description' => 'Witness one of the world\'s most spectacular sunsets from the clifftop village of Oia in Santorini. This picturesque Greek island paradise features iconic blue-domed churches, whitewashed buildings carved into volcanic cliffs, and breathtaking views over the Aegean Sea. Explore charming narrow streets, browse local art galleries, enjoy world-class cuisine, and capture Instagram-worthy photos at every turn.',
        'notes' => 'Sunset viewing spots fill up early - arrive 2 hours before sunset. Best months: April-June and September-October. Try local wines and fresh seafood while watching the sunset.'
    ],
    [
        'name' => 'Machu Picchu',
        'city' => 'Cusco Region',
        'country' => 'PE',
        'latitude' => -13.1631,
        'longitude' => -72.5450,
        'description' => 'Discover the breathtaking ancient Incan citadel perched high in the Andes Mountains, one of the New Seven Wonders of the World. This archaeological marvel offers stunning panoramic views, mysterious stone structures, and an unforgettable journey through history. Hike the famous Inca Trail, explore terraced gardens, and marvel at the precision of ancient engineering in this UNESCO World Heritage site.',
        'notes' => 'Advance booking essential - limited daily visitors. Best time: May-September (dry season). Consider arriving on the first train to avoid crowds. Altitude: 2,430m - acclimatize in Cusco first.'
    ],
    [
        'name' => 'Burj Khalifa Observation Deck',
        'city' => 'Dubai',
        'country' => 'AE',
        'latitude' => 25.1972,
        'longitude' => 55.2744,
        'description' => 'Ascend to the world\'s tallest building and experience unparalleled views from the observation decks on the 124th, 125th, and 148th floors. The Burj Khalifa stands as a testament to human ambition and engineering prowess. Enjoy 360-degree views of Dubai\'s stunning skyline, desert, and coastline. Visit during sunset for a magical transition from day to night as the city lights begin to twinkle below.',
        'notes' => 'Book "At the Top SKY" for highest public observation deck at 555m. Prime time slots (sunset) cost more but worth it. Dubai Mall and fountain show at the base. Best weather: November-March.'
    ],
    [
        'name' => 'Christ the Redeemer',
        'city' => 'Rio de Janeiro',
        'country' => 'BR',
        'latitude' => -22.9519,
        'longitude' => -43.2105,
        'description' => 'Stand in awe beneath the iconic 30-meter Christ the Redeemer statue atop Corcovado Mountain, one of the New Seven Wonders of the World. This Art Deco masterpiece offers breathtaking 360-degree views of Rio de Janeiro, including Copacabana Beach, Sugarloaf Mountain, and the sprawling city below. The journey up through Tijuca National Forest on the cog train adds to the adventure.',
        'notes' => 'Take the scenic cog train through the rainforest. Best time: early morning or late afternoon for softer light and fewer crowds. Can get cloudy quickly - check weather. Combine with Sugarloaf Mountain visit.'
    ],
    [
        'name' => 'Angkor Wat',
        'city' => 'Siem Reap',
        'country' => 'KH',
        'latitude' => 13.4125,
        'longitude' => 103.8670,
        'description' => 'Explore the magnificent temple complex of Angkor Wat, the world\'s largest religious monument and a UNESCO World Heritage site. This 12th-century Khmer empire masterpiece showcases incredible architecture, intricate bas-reliefs, and spiritual significance. Watch the sunrise over the temple spires, explore hidden chambers, and discover the mysterious faces of Bayon Temple in this archaeological wonderland.',
        'notes' => 'Sunrise viewing requires 5 AM start but worth it. 3-day pass recommended to explore multiple temples. Hire a knowledgeable guide to understand the history. Best months: November-February (cool season).'
    ],
    [
        'name' => 'Great Wall of China at Badaling',
        'city' => 'Beijing',
        'country' => 'CN',
        'latitude' => 40.3584,
        'longitude' => 116.0175,
        'description' => 'Walk along one of humanity\'s greatest architectural achievements, the Great Wall of China. The Badaling section offers the most accessible and well-preserved experience of this ancient wonder. Stretching over 13,000 miles, the wall provides spectacular mountain views, historical significance, and photo opportunities. Experience the marvel of ancient engineering while imagining the countless workers who built this defensive masterpiece.',
        'notes' => 'Badaling is most popular but crowded - visit early morning. Mutianyu section less crowded with cable car option. Wear comfortable shoes for steep climbs. Avoid Chinese national holidays for smaller crowds.'
    ],
    [
        'name' => 'Sydney Opera House',
        'city' => 'Sydney',
        'country' => 'AU',
        'latitude' => -33.8568,
        'longitude' => 151.2153,
        'description' => 'Marvel at the architectural wonder of Sydney Opera House, an iconic symbol of Australia with its distinctive shell-shaped design. Take a guided tour to explore the concert halls, attend a world-class performance, or simply admire the building from Circular Quay. The Opera House offers stunning harbor views, fine dining experiences, and cultural performances ranging from opera to contemporary music.',
        'notes' => 'Book performances well in advance. Best views from Circular Quay, Royal Botanic Gardens, or Sydney Harbour Bridge. Guided tours available daily. Combine with harbor cruise for full Sydney experience.'
    ],
    [
        'name' => 'Pyramids of Giza',
        'city' => 'Cairo',
        'country' => 'EG',
        'latitude' => 29.9792,
        'longitude' => 31.1342,
        'description' => 'Stand before the last remaining Wonder of the Ancient World, the Great Pyramid of Giza. These 4,500-year-old monuments showcase ancient Egyptian engineering brilliance alongside the mysterious Sphinx. Explore the pyramid interiors, learn about pharaohs and ancient burial practices, and witness the spectacular sound and light show that brings the monuments to life after dark.',
        'notes' => 'Visit early morning to avoid heat and crowds. Entry tickets to pyramid interiors sold separately and limited. Camel rides available but negotiate prices. Sound and light show in multiple languages nightly.'
    ],
    [
        'name' => 'Banff National Park',
        'city' => 'Banff',
        'country' => 'CA',
        'latitude' => 51.4968,
        'longitude' => -115.9281,
        'description' => 'Immerse yourself in the pristine wilderness of Canada\'s first national park, featuring turquoise lakes, snow-capped peaks, and abundant wildlife. Banff offers year-round outdoor adventures including hiking, skiing, wildlife viewing, and hot springs relaxation. Visit iconic Lake Louise, take the Banff Gondola for panoramic mountain views, and experience the charm of Banff townsite nestled in the heart of the Canadian Rockies.',
        'notes' => 'Peak season: June-August for hiking, December-March for skiing. Lake Louise and Moraine Lake are must-sees. Book accommodations well in advance. Wildlife viewing opportunities include elk, bears, and mountain goats.'
    ],
    [
        'name' => 'Colosseum',
        'city' => 'Rome',
        'country' => 'IT',
        'latitude' => 41.8902,
        'longitude' => 12.4922,
        'description' => 'Step into ancient history at Rome\'s iconic Colosseum, the largest amphitheater ever built. This 2,000-year-old architectural marvel once hosted gladiatorial contests and public spectacles for up to 80,000 spectators. Explore the arena floor, underground chambers, and upper tiers while imagining the roar of ancient crowds. The adjacent Roman Forum and Palatine Hill complete this journey through the heart of the Roman Empire.',
        'notes' => 'Skip-the-line tickets essential, especially in summer. Underground and arena floor tours provide unique perspectives. Combine with Roman Forum and Palatine Hill. Best light for photos: early morning or late afternoon.'
    ],
    [
        'name' => 'Victoria Falls',
        'city' => 'Livingstone',
        'country' => 'ZM',
        'latitude' => -17.9243,
        'longitude' => 25.8563,
        'description' => 'Experience the thundering power of Victoria Falls, one of the world\'s largest waterfalls known locally as "Mosi-oa-Tunya" (The Smoke That Thunders). Watch as the Zambezi River plunges 108 meters into a narrow gorge, creating massive spray clouds visible from kilometers away. Adventure activities include bungee jumping, white-water rafting, helicopter flights, and sunset cruises on the Zambezi River.',
        'notes' => 'Peak flow: March-May (high water season). Dry season (August-December) offers better views of the falls structure. Zambian side offers closer views, Zimbabwean side provides better overall perspective. Raincoats recommended for spray.'
    ],
    [
        'name' => 'Golden Gate Bridge',
        'city' => 'San Francisco',
        'country' => 'US',
        'latitude' => 37.8199,
        'longitude' => -122.4783,
        'description' => 'Marvel at the engineering and artistic beauty of San Francisco\'s Golden Gate Bridge, an Art Deco suspension bridge spanning the Golden Gate strait. Walk or bike across the 2.7-kilometer span for stunning views of San Francisco Bay, Alcatraz Island, and the city skyline. The bridge\'s International Orange color creates a striking contrast against the blue waters and often dramatic fog formations.',
        'notes' => 'Best viewpoints: Crissy Field, Battery Spencer, and Marin Headlands. Early morning offers clearest views before fog rolls in. Walking the bridge is free, parking can be challenging. Combine with visits to nearby Sausalito or Muir Woods.'
    ]
];

// Insert featured locations
$successCount = 0;
$admin_user_id = 1; // Using admin user as the creator of featured locations

foreach ($featuredLocations as $index => $location) {
    try {
        $locationData = [
            'name' => $location['name'],
            'city' => $location['city'],
            'country' => $location['country'],
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'description' => $location['description'],
            'privacy' => 'public',
            'user_id' => $admin_user_id,
            'featured' => 1, // Mark as featured
            'notes' => $location['notes'],
            'approval_status' => 'approved' // Auto-approve featured locations
        ];
        
        $destinationId = $destinationModel->create($locationData);
        
        if ($destinationId) {
            $successCount++;
            echo "✓ Added: {$location['name']}, {$location['city']}, {$location['country']}\n";
        } else {
            echo "✗ Failed to add: {$location['name']}\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error adding {$location['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Featured Locations Seeder Complete ===\n";
echo "✓ Successfully added $successCount out of " . count($featuredLocations) . " featured locations\n\n";

echo "Featured Locations Summary:\n";
echo "- Times Square, New York, USA - The dazzling crossroads of the world\n";
echo "- Disneyland Paris, France - Europe's most magical destination\n";
echo "- Shibuya Crossing, Tokyo, Japan - World's busiest pedestrian crossing\n";
echo "- Santorini Sunset, Oia, Greece - Spectacular clifftop sunset views\n";
echo "- Machu Picchu, Peru - Ancient Incan citadel in the Andes\n";
echo "- Burj Khalifa, Dubai, UAE - World's tallest building observation deck\n";
echo "- Christ the Redeemer, Rio de Janeiro, Brazil - Iconic mountain-top statue\n";
echo "- Angkor Wat, Cambodia - World's largest religious monument\n";
echo "- Great Wall of China, Beijing, China - Ancient architectural marvel\n";
echo "- Sydney Opera House, Australia - Architectural wonder by the harbor\n";
echo "- Pyramids of Giza, Egypt - Last remaining Ancient Wonder\n";
echo "- Banff National Park, Canada - Pristine mountain wilderness\n";
echo "- Colosseum, Rome, Italy - Ancient amphitheater of gladiators\n";
echo "- Victoria Falls, Zambia - Thundering waterfall spectacle\n";
echo "- Golden Gate Bridge, San Francisco, USA - Art Deco suspension bridge\n\n";

echo "All featured locations are:\n";
echo "- Marked as 'featured' for special display\n";
echo "- Set to 'public' privacy with 'approved' status\n";
echo "- Assigned to admin user (ID: $admin_user_id)\n";
echo "- Include detailed descriptions and insider tips\n";
echo "- Cover major continents and diverse experiences\n\n";

echo "🎉 Ready to explore the world's most amazing destinations!\n";
?>

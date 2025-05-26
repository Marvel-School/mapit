<?php
// Destinations Index view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">My Destinations</h4>
                        <a href="/destinations/create" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add Destination
                        </a>
                    </div>
                </div>                <div class="card-body p-0">
                    <div id="destinations-map" class="map-container mb-4"></div>
                    <div class="px-3 py-2 border-top bg-light">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Tip:</strong> Click anywhere on the map to quickly add a new destination
                        </small>
                    </div>
                    
                    <div class="p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search destinations...">
                                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filterStatus">
                                    <option value="">All Statuses</option>
                                    <option value="visited">Visited</option>
                                    <option value="wishlist">Wishlist</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filterCountry">
                                    <option value="">All Countries</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= $country['code']; ?>"><?= htmlspecialchars($country['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="sortBy">
                                    <option value="name">Name</option>
                                    <option value="date_desc">Newest</option>
                                    <option value="date_asc">Oldest</option>
                                    <option value="country">Country</option>
                                </select>
                            </div>
                        </div>
                        
                        <?php if (empty($destinations)): ?>
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <img src="/images/empty-destinations.svg" alt="No destinations" style="max-width: 200px;">
                                </div>
                                <h5>You haven't added any destinations yet</h5>
                                <p class="text-muted">Start building your travel map by adding places you've visited or want to visit.</p>
                                <a href="/destinations/create" class="btn btn-primary">Add Your First Destination</a>
                            </div>
                        <?php else: ?>
                            <div class="row" id="destinationsContainer">
                                <?php foreach ($destinations as $destination): ?>
                                    <div class="col-md-6 col-lg-4 mb-4 destination-card" 
                                         data-name="<?= strtolower(htmlspecialchars($destination['name'])); ?>"
                                         data-country="<?= htmlspecialchars($destination['country']); ?>"
                                         data-status="<?= $destination['visited'] ? 'visited' : 'wishlist'; ?>">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <?php if (!empty($destination['image'])): ?>
                                                <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" class="card-img-top destination-img" alt="<?= htmlspecialchars($destination['name']); ?>">
                                            <?php else: ?>
                                                <img src="/images/destination-placeholder.jpg" class="card-img-top destination-img" alt="<?= htmlspecialchars($destination['name']); ?>">
                                            <?php endif; ?>
                                            
                                            <?php if ($destination['visited']): ?>
                                                <div class="destination-badge bg-success text-white">
                                                    <i class="fas fa-check"></i> Visited
                                                </div>
                                            <?php else: ?>
                                                <div class="destination-badge bg-warning text-white">
                                                    <i class="fas fa-heart"></i> Wishlist
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($destination['name']); ?></h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?= htmlspecialchars($destination['city'] . ', ' . $destination['country_name']); ?>
                                                </p>
                                                
                                                <?php if (!empty($destination['visit_date']) && $destination['visited']): ?>
                                                    <p class="card-text small">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Visited on: <?= date('M d, Y', strtotime($destination['visit_date'])); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <p class="card-text">
                                                    <?= htmlspecialchars(substr($destination['description'], 0, 100) . '...'); ?>
                                                </p>
                                                
                                                <div class="d-flex justify-content-between">
                                                    <a href="/destinations/<?= $destination['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                                    
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                                            <li><a class="dropdown-item" href="/destinations/<?= $destination['id']; ?>/edit">
                                                                <i class="fas fa-edit me-2"></i> Edit
                                                            </a></li>
                                                            
                                                            <?php if ($destination['visited']): ?>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $destination['id']; ?>, 0); return false;">
                                                                    <i class="fas fa-heart me-2"></i> Move to Wishlist
                                                                </a></li>
                                                            <?php else: ?>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $destination['id']; ?>, 1); return false;">
                                                                    <i class="fas fa-check me-2"></i> Mark as Visited
                                                                </a></li>
                                                            <?php endif; ?>
                                                            
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" 
                                                                   data-bs-toggle="modal" 
                                                                   data-bs-target="#deleteModal" 
                                                                   data-id="<?= $destination['id']; ?>" 
                                                                   data-name="<?= htmlspecialchars($destination['name']); ?>">
                                                                <i class="fas fa-trash-alt me-2"></i> Delete
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $currentPage - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?= $currentPage == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $currentPage + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <span id="destinationName"></span>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Destination Modal -->
<div class="modal fade" id="quickAddDestinationModal" tabindex="-1" aria-labelledby="quickAddDestinationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddDestinationModalLabel">Add Destination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickAddDestinationForm">
                    <div class="mb-3">
                        <label for="quickDestinationName" class="form-label">Destination Name *</label>
                        <input type="text" class="form-control" id="quickDestinationName" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quickDestinationCity" class="form-label">City</label>
                                <input type="text" class="form-control" id="quickDestinationCity" name="city">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quickDestinationCountry" class="form-label">Country</label>
                                <select class="form-select" id="quickDestinationCountry" name="country">
                                    <option value="">Select Country</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= $country['code']; ?>"><?= htmlspecialchars($country['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quickDestinationDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="quickDestinationDescription" name="description" rows="3"></textarea>
                    </div>
                      <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quickDestinationStatus" class="form-label">Status</label>
                                <select class="form-select" id="quickDestinationStatus" name="visited">
                                    <option value="0">Wishlist</option>
                                    <option value="1">Visited</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quickDestinationPrivacy" class="form-label">Privacy</label>
                                <select class="form-select" id="quickDestinationPrivacy" name="privacy">
                                    <option value="private">Private</option>
                                    <option value="public">Public (needs approval)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="visit-date-container" style="display: none;">
                        <div class="mb-3">
                            <label for="quickDestinationVisitDate" class="form-label">Visit Date</label>
                            <input type="date" class="form-control" id="quickDestinationVisitDate" name="visit_date">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Location: <span id="selectedCoordinates">Click on the map to set location</span>
                        </small>
                    </div>
                    
                    <input type="hidden" id="quickDestinationLat" name="latitude">
                    <input type="hidden" id="quickDestinationLng" name="longitude">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveQuickDestination">Save Destination</button>
            </div>
        </div>
    </div>
</div>

<style>
    .map-container {
        height: 400px;
        width: 100%;
    }
    
    .destination-img {
        height: 180px;
        object-fit: cover;
    }
    
    .destination-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Google Maps API is loaded
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.error('Google Maps API not loaded');
        const mapContainer = document.getElementById('destinations-map');
        if (mapContainer) {
            mapContainer.innerHTML = '<div class="alert alert-warning">Map could not be loaded. Google Maps API key may be missing.</div>';
        }
        return;
    }
    
    // Initialize the map
    initDestinationsMap();
    
    // Initialize quick destination create functionality
    initializeQuickDestinationCreate();
    
    // Setup delete modal
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            
            document.getElementById('destinationName').textContent = name;
            document.getElementById('deleteForm').action = `/destinations/${id}/delete`;
        });
    }
    
    // Setup filtering
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const filterStatus = document.getElementById('filterStatus');
    const filterCountry = document.getElementById('filterCountry');
    const sortBy = document.getElementById('sortBy');
    
    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;
        const countryFilter = filterCountry.value;
        
        const destinationCards = document.querySelectorAll('.destination-card');
        
        destinationCards.forEach(card => {
            const name = card.getAttribute('data-name');
            const country = card.getAttribute('data-country');
            const status = card.getAttribute('data-status');
            
            let showCard = true;
            
            if (searchText && !name.includes(searchText)) {
                showCard = false;
            }
            
            if (statusFilter && status !== statusFilter) {
                showCard = false;
            }
            
            if (countryFilter && country !== countryFilter) {
                showCard = false;
            }
            
            card.style.display = showCard ? '' : 'none';
        });
    }
    
    if (searchButton) searchButton.addEventListener('click', applyFilters);
    if (searchInput) searchInput.addEventListener('keyup', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);
    if (filterCountry) filterCountry.addEventListener('change', applyFilters);
    
    // Setup sorting
    if (sortBy) {
        sortBy.addEventListener('change', function() {
            const container = document.getElementById('destinationsContainer');
            const cards = Array.from(container.querySelectorAll('.destination-card'));
            
            cards.sort(function(a, b) {
                switch(sortBy.value) {
                    case 'name':
                        return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                    case 'country':
                        return a.getAttribute('data-country').localeCompare(b.getAttribute('data-country'));
                    case 'date_desc':
                    case 'date_asc':
                        // You would need to add a data-date attribute to sort by date
                        // This is placeholder logic
                        return sortBy.value === 'date_desc' ? -1 : 1;
                }
            });
            
            cards.forEach(card => container.appendChild(card));
        });
    }
      // Function to initialize the map with user's destinations    function initDestinationsMap() {
        const mapContainer = document.getElementById('destinations-map');
        
        if (!mapContainer) {
            console.error('Destinations map container not found');
            return;
        }
        
        try {
            // Check if Google Maps is available
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                console.error('Google Maps API not loaded');
                mapContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <h5>Map could not be loaded. Google Maps API is not available.</h5>
                        <p>Please check your internet connection and try again.</p>
                        <button class="btn btn-sm btn-danger mt-2" onclick="window.location.reload()">Reload Page</button>
                    </div>
                `;
                  // Try to load Google Maps
                if (typeof waitForGoogleMaps === 'function') {
                    waitForGoogleMaps();
                }
                return;
            }
            
            window.destinationsMap = new google.maps.Map(mapContainer, {
                zoom: 2,
                center: {lat: 20, lng: 0},
                mapTypeId: 'terrain',
                mapTypeControl: false,
                streetViewControl: false
            });
              // Use the destinations data passed from PHP instead of making an AJAX call
            const userDestinations = <?= json_encode($userDestinations ?? []); ?>;
            const publicDestinations = <?= json_encode($publicDestinations ?? []); ?>;              // Combine user and public destinations for the map
            const allDestinations = [...userDestinations, ...publicDestinations];
            window.addDestinationsToMap(window.destinationsMap, allDestinations);
            
            // Enable interactive map clicking for adding destinations
            enableInteractiveMapClicking(window.destinationsMap);
        } catch (error) {
            console.error('Error initializing destinations map:', error);
            mapContainer.innerHTML = `
                <div class="alert alert-danger">
                    <h5>Error initializing map</h5>
                    <p>${error.message || 'Unknown error'}</p>
                    <button class="btn btn-sm btn-danger mt-2" onclick="window.location.reload()">Reload Page</button>
                </div>
            `;
              // Log the error (server logging removed for performance)
            console.error('Error initializing destinations map:', error.message);
        }
    }    // Function to add destinations to the map (make it globally accessible)
    window.addDestinationsToMap = function addDestinationsToMap(map, destinations) {
        const bounds = new google.maps.LatLngBounds();
        const visitedIcon = {
            url: '/images/markers/visited.png',
            scaledSize: new google.maps.Size(32, 32)
        };
        const wishlistIcon = {
            url: '/images/markers/wishlist.png',
            scaledSize: new google.maps.Size(32, 32)
        };
          destinations.forEach(dest => {
            // Skip destinations with invalid coordinates
            if (!dest.latitude || !dest.longitude) {
                console.warn('Skipping destination with invalid coordinates:', dest);
                return;
            }
            
            const position = {
                lat: parseFloat(dest.latitude), 
                lng: parseFloat(dest.longitude)
            };
            
            // Skip if parsing failed
            if (isNaN(position.lat) || isNaN(position.lng)) {
                console.warn('Skipping destination with invalid coordinate format:', dest);
                return;
            }
              // Determine if visited (check multiple possible properties)
            const isVisited = dest.visited || (dest.trip_count && dest.trip_count > 0) || dest.status === 'visited';
            
            // Create marker element for AdvancedMarkerElement
            const markerElement = document.createElement('div');
            markerElement.innerHTML = `
                <img src="/images/markers/${isVisited ? 'visited' : 'wishlist'}.png" 
                     style="width: 32px; height: 32px;" 
                     alt="${isVisited ? 'Visited' : 'Wishlist'} destination"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="
                    display: none;
                    width: 16px; 
                    height: 16px; 
                    background: ${isVisited ? '#28a745' : '#ffc107'}; 
                    border: 2px solid white; 
                    border-radius: 50%;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                "></div>
            `;
            
            // Create marker using new API if available, fallback to legacy
            let marker;
            try {
                if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: position,
                        map: map,
                        title: dest.name,
                        content: markerElement
                    });
                } else {
                    // Fallback to legacy marker
                    marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: dest.name,
                        icon: isVisited ? visitedIcon : wishlistIcon
                    });
                }
            } catch (error) {
                console.warn('Error creating advanced marker, using legacy marker:', error);
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: dest.name,
                    icon: isVisited ? visitedIcon : wishlistIcon
                });
            }
            
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="info-window">
                        <h5>${dest.name}</h5>
                        <p>${dest.city || 'Unknown'}, ${dest.country || 'Unknown'}</p>
                        <a href="/destinations/${dest.id}" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                `
            });
              // Add click listener based on marker type
            if (google.maps.marker && google.maps.marker.AdvancedMarkerElement &&
                marker instanceof google.maps.marker.AdvancedMarkerElement) {
                marker.addEventListener('click', () => {
                    infoWindow.open({
                        anchor: marker,
                        map: map
                    });
                });
            } else {
                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });
            }
            
            bounds.extend(position);
        });
        
        // Only adjust bounds if we have destinations
        if (destinations.length > 0) {
            map.fitBounds(bounds);
            
            // Prevent zooming in too much for single destinations
            const listener = google.maps.event.addListener(map, 'idle', function() {
                if (map.getZoom() > 12) {
                    map.setZoom(12);
                }
                google.maps.event.removeListener(listener);        });        }
    };
});

// Function to update destination status
function updateStatus(id, visited) {
    // You'd likely use AJAX to update the status
    // This is a placeholder to show how it might work
    fetch(`/api/destinations/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ visited: visited })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to update destination status');
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        alert('An error occurred while updating the destination status');
    });
}
</script>

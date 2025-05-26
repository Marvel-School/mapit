/**
 * MapIt - Google Maps Debug Helper
 * 
 * This script adds enhanced logging and debugging capabilities for Google Maps
 * to help diagnose and fix loading and authentication issues.
 */

// Original console functions
const originalConsole = {
    log: console.log,
    error: console.error,
    warn: console.warn,
    info: console.info
};

// Store logs for retrieval
const mapitDebugLogs = {
    general: [],
    errors: [],
    warnings: [],
    apiCalls: []
};

// Enhanced console logging with timestamps and categories
console.log = function() {
    originalConsole.log.apply(console, arguments);
    const message = Array.from(arguments).join(' ');
    const timestamp = new Date().toISOString();
    mapitDebugLogs.general.push({ timestamp, message });
    
    // Send to server logging if enabled
    if (window.MAPIT_DEBUG_MODE) {
        logToServer('info', message);
    }
};

console.error = function() {
    originalConsole.error.apply(console, arguments);
    const message = Array.from(arguments).join(' ');
    const timestamp = new Date().toISOString();
    mapitDebugLogs.errors.push({ timestamp, message });
    
    // Add visual feedback for map-related errors
    if (message.includes('Maps') || message.includes('map')) {
        showMapError(message);
    }
    
    // Send to server logging if enabled
    if (window.MAPIT_DEBUG_MODE) {
        logToServer('error', message);
    }
};

console.warn = function() {
    originalConsole.warn.apply(console, arguments);
    const message = Array.from(arguments).join(' ');
    const timestamp = new Date().toISOString();
    mapitDebugLogs.warnings.push({ timestamp, message });
    
    // Send to server logging if enabled
    if (window.MAPIT_DEBUG_MODE) {
        logToServer('warning', message);
    }
};

// Detailed API key logging
function logApiKeyStatus(apiKey) {
    if (!apiKey) {
        console.error('Maps API Key Missing: No API key found');
        return false;
    }
    
    if (apiKey.length < 10) {
        console.error('Maps API Key Invalid: Key appears too short');
        return false;
    }
    
    console.log('Maps API Key Found: Key starts with ' + apiKey.substring(0, 5) + '...');
    return true;
}

// Show visual error on map containers
function showMapError(message) {
    // Find all map containers
    const mapContainers = document.querySelectorAll('[id$="-map"]');
    if (mapContainers.length === 0) return;
    
    mapContainers.forEach(container => {
        // Only add error message if container doesn't already have an error
        if (!container.querySelector('.mapit-map-error')) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mapit-map-error';
            errorDiv.innerHTML = `
                <strong>Map Error:</strong> ${message}
                <br><small>Check browser console for more details.</small>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" 
                        onclick="window.location.reload()">Reload Page</button>
            `;
            container.appendChild(errorDiv);
            
            // Make sure the error is visible
            container.style.position = 'relative';
            errorDiv.style.position = 'absolute';
            errorDiv.style.top = '50%';
            errorDiv.style.left = '50%';
            errorDiv.style.transform = 'translate(-50%, -50%)';
            errorDiv.style.zIndex = '1000';
            errorDiv.style.maxWidth = '80%';
        }
    });
}

// Send logs to server for debugging
function logToServer(level, message) {
    try {
        const data = {
            level,
            message,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
        };
        
        fetch('/api/debug/log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        }).catch(err => {
            // Silent fail - we don't want to cause a loop of errors
        });
    } catch (e) {
        // Silent fail
    }
}

// Enhanced Google Maps error handler
function gm_authFailure() {
    const apiKey = document.querySelector('meta[name="google-maps-api-key"]')?.getAttribute('content');
    const errorMsg = 'Google Maps API authentication failed. The API key may be invalid, missing, or have restrictions.';
    
    console.error(errorMsg);
    console.error('API Key Check:', apiKey ? 'Present (starts with ' + apiKey.substring(0, 5) + '...)' : 'Missing');
    
    // Check for referrer restrictions
    console.warn('Referrer Check: Your API key may have referrer restrictions that prevent it from working in this environment.');
    console.warn('Current URL:', window.location.href);
    
    // Display error on all map containers
    const mapContainers = document.querySelectorAll('[id$="-map"]');
    mapContainers.forEach(container => {
        container.innerHTML = `
            <div class="alert alert-danger p-4">
                <h5>Map could not be loaded. Google Maps API key may be invalid.</h5>
                <p>Please check the browser console for more details.</p>
                <button class="btn btn-sm btn-danger" onclick="window.location.reload()">Reload Page</button>
            </div>
        `;
    });
    
    // Log to server
    logToServer('error', 'Google Maps API authentication failure: ' + 
        (apiKey ? 'API Key present (starts with ' + apiKey.substring(0, 5) + '...)' : 'API Key missing'));
}

// Monitor API loading
function monitorMapsApiLoading() {
    let checkCount = 0;
    const maxChecks = 20;
    const checkInterval = 500; // 500ms
    
    const checkStatus = () => {
        checkCount++;
        if (typeof google !== 'undefined' && google.maps) {
            console.log(`Google Maps API successfully loaded after ${checkCount} checks`);
            return;
        }
        
        if (checkCount >= maxChecks) {
            console.error(`Google Maps API failed to load after ${maxChecks} checks (${maxChecks * checkInterval / 1000}s)`);
            showMapError('Google Maps API failed to load. The page may need to be refreshed.');
            return;
        }
        
        setTimeout(checkStatus, checkInterval);
    };
    
    setTimeout(checkStatus, checkInterval);
}

// Setup debug mode - Called when this script loads
(function setupDebugMode() {
    window.MAPIT_DEBUG_MODE = true;
    console.info('MapIt Maps Debug Mode Activated');
    
    // Start monitoring API loading
    monitorMapsApiLoading();
    
    // Check if Google Maps is already being loaded
    const apiScripts = Array.from(document.scripts).filter(script => 
        script.src && script.src.includes('maps.googleapis.com'));
        
    console.log(`Found ${apiScripts.length} Google Maps API script tags`);
    
    // Check if API Key meta tag exists
    const apiKeyMeta = document.querySelector('meta[name="google-maps-api-key"]');
    if (apiKeyMeta) {
        logApiKeyStatus(apiKeyMeta.getAttribute('content'));
    } else {
        console.error('No Google Maps API Key meta tag found');
    }
    
    // Expose debugging helpers to global scope
    window.MapitDebug = {
        logs: mapitDebugLogs,
        showError: showMapError,
        getAPIStatus: function() {
            const status = {
                apiLoaded: typeof google !== 'undefined' && typeof google.maps !== 'undefined',
                apiKey: document.querySelector('meta[name="google-maps-api-key"]')?.getAttribute('content') || 'Not found',
                mapContainers: document.querySelectorAll('[id$="-map"]').length,
                googleMapsInitialized: window.googleMapsInitialized || false
            };
            console.table(status);
            return status;
        }
    };
})();

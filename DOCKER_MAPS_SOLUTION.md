# Google Maps API Fix for Docker Environments

This document provides a comprehensive solution for fixing Google Maps API issues in Docker container environments for the MapIt application.

## The Problem

When running the MapIt application in a Docker container:
- The map loads initially on the dashboard but shows errors when interacting with it: "This page can't load Google Maps correctly."
- The map doesn't load at all on the destinations page, showing: "Map could not be loaded. Google Maps API key may be missing."

## Root Causes

1. **HTTP Referrer Restrictions**: Google Maps API keys are often restricted to specific HTTP referrers. Docker containers may have different hostnames/IPs than what's allowed in these restrictions.

2. **API Key Access**: The Docker container might be accessing the Google Maps API from an IP address that's not on the allowed list in Google Cloud Console.

3. **Script Loading Order**: The asynchronous loading of Google Maps API script may not be properly handled in Docker container environments, leading to race conditions.

## Solution Components

The following enhancements have been implemented to fix the Google Maps API issues in Docker:

### 1. Docker-Specific Maps Loader (`docker-maps-loader.js`)

A specialized script that handles Docker-specific challenges:
- Implements exponential backoff retries for network issues
- Tries different library combinations to find what works
- Provides detailed error logging

### 2. Environment Detection API (`EnvironmentController.php`)

Server-side endpoint that:
- Detects if the application is running in Docker
- Provides container information (IP, hostname)
- Helps the client-side decide which loading strategy to use

### 3. Enhanced Destinations Map Handler (`destinations-map.js`)

Improves map initialization on the destinations page:
- Uses Docker-specific loading strategy when needed
- Provides better error handling and visualization
- Implements fallback mechanisms

### 4. HTTP Referrer Checker (`http_referrer_checker.php`)

Diagnostic tool that:
- Tests API access with different HTTP referrers
- Identifies issues with API key restrictions
- Provides recommendations based on findings

## Setup Instructions

### Step 1: Update API Key Restrictions

1. Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Find your Maps API key and click "Edit"
3. Under "Application restrictions":
   - If using HTTP referrers, add:
     - `http://localhost/*`
     - `http://[docker-container-ip]/*`
     - `http://[your-domain]/*`
   - If using IP addresses, add your Docker host's external IP

### Step 2: Update Docker Configuration

Ensure your Docker setup includes the necessary environment variables:

```yaml
# In docker-compose.yml
services:
  php:
    environment:
      - GOOGLE_MAPS_API_KEY=${GOOGLE_MAPS_API_KEY}
```

### Step 3: Deploy the New Scripts

The following files have been created or modified to fix the issue:

- `public/js/docker-maps-loader.js` - Docker-specific Maps loader
- `app/Controllers/Api/EnvironmentController.php` - Docker environment detection
- `public/js/destinations-map.js` - Enhanced map initialization 
- `public/http_referrer_checker.php` - API key restriction diagnostics
- `app/Views/layouts/main.php` - Updated script loading

## Testing and Verification

1. Visit `/http_referrer_checker.php` to check for HTTP referrer issues
2. Use the dashboard and destinations pages to verify maps are loading
3. Check browser console for any remaining map-related errors

## Troubleshooting

If issues persist:

1. **API Key Restrictions**: Temporarily remove all restrictions from your API key to test if that's the issue
2. **Network Access**: Ensure the Docker container has outbound internet access
3. **Script Loading**: Check browser console for loading errors and timing issues
4. **Docker Setup**: Verify environment variables are correctly passed to the container

## Additional Resources

- [Google Maps JavaScript API Documentation](https://developers.google.com/maps/documentation/javascript/overview)
- [API Key Best Practices](https://developers.google.com/maps/documentation/javascript/get-api-key#restrict_key)
- [Docker Networking Guide](https://docs.docker.com/network/)

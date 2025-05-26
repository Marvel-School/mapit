#!/bin/bash

# MapIt Deployment Script
# This script automates the deployment process

set -e

# Configuration
APP_NAME="mapit"
DOCKER_COMPOSE_FILE="docker-compose.yml"
BACKUP_DIR="./backups"
LOG_FILE="./deployment.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a "$LOG_FILE"
}

# Function to check prerequisites
check_prerequisites() {
    log "Checking prerequisites..."
    
    if ! command -v docker &> /dev/null; then
        error "Docker is not installed or not in PATH"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        error "Docker Compose is not installed or not in PATH"
        exit 1
    fi
    
    log "Prerequisites check passed"
}

# Function to create backup
create_backup() {
    if [ "$1" = "skip-backup" ]; then
        log "Skipping backup as requested"
        return
    fi
    
    log "Creating backup..."
    
    mkdir -p "$BACKUP_DIR"
    BACKUP_NAME="mapit_backup_$(date +'%Y%m%d_%H%M%S')"
    
    # Backup database
    if [ -f "./database/mapit.db" ]; then
        cp "./database/mapit.db" "$BACKUP_DIR/${BACKUP_NAME}_database.db"
        log "Database backup created: $BACKUP_DIR/${BACKUP_NAME}_database.db"
    fi
    
    # Backup uploads
    if [ -d "./storage/uploads" ]; then
        tar -czf "$BACKUP_DIR/${BACKUP_NAME}_uploads.tar.gz" -C "./storage" uploads/
        log "Uploads backup created: $BACKUP_DIR/${BACKUP_NAME}_uploads.tar.gz"
    fi
    
    log "Backup completed"
}

# Function to deploy application
deploy() {
    log "Starting deployment..."
    
    # Build and start containers
    log "Building Docker images..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" build
    
    log "Starting containers..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" up -d
    
    # Wait for services to be ready
    log "Waiting for services to be ready..."
    sleep 10
    
    # Check if services are running
    if docker-compose -f "$DOCKER_COMPOSE_FILE" ps | grep -q "Up"; then
        log "Deployment successful!"
        log "Application is available at: http://localhost:8080"
    else
        error "Deployment failed - some services are not running"
        docker-compose -f "$DOCKER_COMPOSE_FILE" logs
        exit 1
    fi
}

# Function to run tests
run_tests() {
    log "Running tests..."
    
    # Check if app container is running
    if ! docker-compose -f "$DOCKER_COMPOSE_FILE" ps app | grep -q "Up"; then
        error "App container is not running. Cannot run tests."
        return 1
    fi
    
    # Run PHPUnit tests inside the container
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec app php vendor/bin/phpunit --configuration phpunit.xml
    
    if [ $? -eq 0 ]; then
        log "All tests passed!"
    else
        error "Some tests failed"
        return 1
    fi
}

# Function to show status
show_status() {
    log "Application Status:"
    docker-compose -f "$DOCKER_COMPOSE_FILE" ps
    
    echo ""
    log "Container logs (last 20 lines):"
    docker-compose -f "$DOCKER_COMPOSE_FILE" logs --tail=20
}

# Function to stop application
stop_app() {
    log "Stopping application..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" down
    log "Application stopped"
}

# Function to clean up
cleanup() {
    log "Cleaning up..."
    
    # Remove unused Docker images
    docker image prune -f
    
    # Remove old backups (keep last 5)
    if [ -d "$BACKUP_DIR" ]; then
        cd "$BACKUP_DIR"
        ls -t *.db 2>/dev/null | tail -n +6 | xargs rm -f
        ls -t *.tar.gz 2>/dev/null | tail -n +6 | xargs rm -f
        cd ..
    fi
    
    log "Cleanup completed"
}

# Function to show help
show_help() {
    echo "MapIt Deployment Script"
    echo ""
    echo "Usage: $0 [COMMAND] [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  deploy              Deploy the application"
    echo "  test                Run tests"
    echo "  status              Show application status"
    echo "  stop                Stop the application"
    echo "  restart             Restart the application"
    echo "  cleanup             Clean up old files and Docker images"
    echo "  backup              Create a backup"
    echo "  help                Show this help message"
    echo ""
    echo "Options:"
    echo "  --skip-backup       Skip backup creation during deployment"
    echo "  --no-tests          Skip running tests during deployment"
    echo ""
    echo "Examples:"
    echo "  $0 deploy                    # Full deployment with backup and tests"
    echo "  $0 deploy --skip-backup      # Deploy without creating backup"
    echo "  $0 deploy --no-tests         # Deploy without running tests"
    echo "  $0 restart                   # Restart the application"
}

# Main script logic
main() {
    case "$1" in
        "deploy")
            check_prerequisites
            create_backup "$2"
            deploy
            if [ "$2" != "--no-tests" ] && [ "$3" != "--no-tests" ]; then
                run_tests
            fi
            ;;
        "test")
            run_tests
            ;;
        "status")
            show_status
            ;;
        "stop")
            stop_app
            ;;
        "restart")
            stop_app
            sleep 2
            deploy
            ;;
        "cleanup")
            cleanup
            ;;
        "backup")
            create_backup
            ;;
        "help"|"")
            show_help
            ;;
        *)
            error "Unknown command: $1"
            show_help
            exit 1
            ;;
    esac
}

# Create log file
touch "$LOG_FILE"

# Run main function with all arguments
main "$@"

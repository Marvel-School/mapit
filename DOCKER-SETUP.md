# MapIt Docker Development Setup

This guide will help you set up the MapIt application for local development using Docker.

## Prerequisites

- Docker Desktop for Windows
- PowerShell (included with Windows)
- Git (for version control)

## Quick Start

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd mapit
   ```

2. **Copy environment configuration**:
   ```powershell
   Copy-Item .env.local .env
   ```

3. **Start the application**:
   ```powershell
   .\docker-dev.ps1 up
   ```

4. **Install dependencies**:
   ```powershell
   .\docker-dev.ps1 install
   ```

5. **Seed the database**:
   ```powershell
   .\docker-dev.ps1 seed
   ```

6. **Access the application**:
   - Website: http://localhost
   - Admin Panel: http://localhost/admin (admin@mapit.com / admin123)

## Docker Services

The application consists of three main services:

### 1. PHP-FPM (`php`)
- **Container**: `mapit_php`
- **Base Image**: PHP 8.1-FPM
- **Includes**: Composer, SQLite, MySQL extensions
- **Volume**: Project directory mounted to `/var/www/html`

### 2. Nginx (`nginx`)
- **Container**: `mapit_nginx`
- **Ports**: 80 (HTTP), 443 (HTTPS)
- **Configuration**: Custom nginx configuration for PHP-FPM

### 3. MySQL (`mysql`)
- **Container**: `mapit_mysql`
- **Port**: 3306
- **Database**: `mapit`
- **User**: `mapit_user` / `mapit_password`
- **Data**: Persistent volume (`mysql_data`)

## Development Commands

Use the `docker-dev.ps1` script for common operations:

```powershell
# Start services
.\docker-dev.ps1 up

# Stop services
.\docker-dev.ps1 down

# View logs
.\docker-dev.ps1 logs

# Access PHP container shell
.\docker-dev.ps1 shell

# Access MySQL shell
.\docker-dev.ps1 mysql

# Install Composer dependencies
.\docker-dev.ps1 install

# Run database seeder
.\docker-dev.ps1 seed

# Run tests
.\docker-dev.ps1 test

# Clean up Docker resources
.\docker-dev.ps1 clean
```

## Database Configuration

### MySQL (Default)
The application uses MySQL by default with the following configuration:
- **Host**: `mysql` (container name)
- **Port**: `3306`
- **Database**: `mapit`
- **Username**: `mapit_user`
- **Password**: `mapit_password`

### SQLite (Alternative)
To use SQLite instead of MySQL, update your `.env` file:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/mapit.db
```

## File Structure

```
docker/
├── nginx/
│   ├── nginx.conf          # Main nginx configuration
│   └── default.conf        # Site configuration
├── php/
│   ├── Dockerfile          # PHP-FPM container
│   └── php.ini            # PHP configuration
└── mysql/
    └── init.sql           # Database initialization
```

## Environment Configuration

Create a `.env` file based on `.env.local`:

```env
# Application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=mapit
DB_USERNAME=mapit_user
DB_PASSWORD=mapit_password

# Additional settings...
```

## Troubleshooting

### Services won't start
1. Check if ports are available:
   ```powershell
   netstat -an | findstr ":80 "
   netstat -an | findstr ":3306 "
   ```

2. Check Docker logs:
   ```powershell
   .\docker-dev.ps1 logs
   ```

### Database connection issues
1. Ensure MySQL service is healthy:
   ```powershell
   .\docker-dev.ps1 status
   ```

2. Test MySQL connection:
   ```powershell
   .\docker-dev.ps1 mysql
   ```

### Permission issues
1. Reset container ownership:
   ```powershell
   .\docker-dev.ps1 shell
   sudo chown -R www:www /var/www/html
   ```

### Clear everything and start fresh
```powershell
.\docker-dev.ps1 clean
.\docker-dev.ps1 rebuild
.\docker-dev.ps1 up
```

## Development Workflow

1. **Make code changes** in your IDE
2. **Refresh browser** (changes are reflected immediately)
3. **Run tests** to ensure functionality:
   ```powershell
   .\docker-dev.ps1 test
   ```
4. **Check logs** if needed:
   ```powershell
   .\docker-dev.ps1 logs
   ```

## Performance Tips

- Use **volume mounts** for development (already configured)
- **Disable OPCache** in development (configured in php.ini)
- **Enable debug mode** for detailed error messages
- **Use SQLite** for faster development database operations

## Security Notes

- Default passwords are for **development only**
- Change all credentials for **production deployment**
- The MySQL root password is `root_password`
- Admin user credentials: `admin@mapit.com` / `admin123`

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.configuration.php)
- [Nginx Configuration](https://nginx.org/en/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

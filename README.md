# MapIt - Travel Destination Mapping

A comprehensive travel destination mapping application built with PHP, featuring interactive maps, trip planning, and destination management.

## 🔒 Security Notice

**Repository History Reset**: This repository's commit history was completely reset on June 2, 2025, to remove any accidentally committed sensitive information such as API keys, database credentials, and other configuration data. All previous commits containing sensitive data have been permanently removed.

### Environment Configuration

- **Never commit `.env` files**: The `.env` file contains sensitive configuration and is excluded from version control
- **Use `.env.example`**: Copy `.env.example` to `.env` and update with your actual configuration values
- **GitHub Secrets**: Production deployment credentials are safely stored as GitHub repository secrets and were not affected by the history reset

## 🚀 Quick Start

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Marvel-School/mapit.git
   cd mapit
   ```

2. **Set up environment**:
   ```bash
   cp .env.example .env
   # Edit .env with your configuration values
   ```

3. **Start with Docker**:
   ```bash
   docker-compose up -d
   ```

4. **Access the application**:
   - Local: http://localhost
   - Admin panel: http://localhost/admin

## 📁 Project Structure

```
mapit/
├── app/                    # Application core
│   ├── Controllers/        # Request handlers
│   ├── Models/            # Data models
│   ├── Views/             # UI templates
│   └── Core/              # Framework components
├── config/                # Configuration files
├── docker/                # Docker configurations
├── public/                # Web assets and entry point
└── database/              # Database migrations and scripts
```

## 🛠 Features

- **Interactive Maps**: Google Maps integration for destination visualization
- **Trip Planning**: Create and manage travel itineraries
- **User Management**: Registration, authentication, and profiles
- **Admin Dashboard**: Content and user management
- **Responsive Design**: Mobile-friendly interface
- **Docker Support**: Containerized deployment

## 🚀 Deployment

The application supports automated deployment through GitHub Actions. Production deployment credentials are securely managed through GitHub repository secrets.

See `PRODUCTION_DEPLOYMENT_GUIDE.md` for detailed deployment instructions.

## 🔧 Development

### Requirements

- PHP 8.0+
- MySQL 8.0+ or SQLite
- Docker & Docker Compose (recommended)
- Composer for dependency management

### Local Development

1. Install dependencies:
   ```bash
   composer install
   ```

2. Configure environment:
   ```bash
   cp .env.example .env
   # Update .env with your local settings
   ```

3. Start development server:
   ```bash
   docker-compose up
   ```

## 📝 API Documentation

The application provides a RESTful API for destination and trip management:

- `GET /api/destinations` - List destinations
- `POST /api/destinations` - Create destination
- `GET /api/trips` - List trips
- `POST /api/trips` - Create trip

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Ensure tests pass
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For questions or issues, please open a GitHub issue or contact the development team.

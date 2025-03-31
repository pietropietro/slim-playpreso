# PlayPreso Backend API

Backend service powering the PlayPreso football prediction platform - a gamified application allowing users to predict football match outcomes, compete with friends, and track their performance across various tournaments and leagues.

## Overview

This PHP application serves as the core backend for the PlayPreso platform, handling:

- User authentication and management
- Football match data processing
- Prediction (guess) creation and verification
- Points calculation and leaderboards
- Tournament and league management
- Push notifications
- Statistics and achievements

## Tech Stack

- **Framework**: Slim 3 (PHP 8.2)
- **Database**: MySQL with Redis caching
- **Authentication**: JWT (Firebase PHP-JWT)
- **External Services**:
  - Push notifications (Firebase, APNs via Pushok)
  - External football data APIs
- **Development Tools**:
  - PHPStan, Psalm, and Rector for static analysis
  - Pest/PHPUnit for testing

## Architecture

The application follows a service-oriented architecture with dependency injection:

- **Services**: Organized by domain (User, Match, Guess, etc.)
- **Repositories**: Data access layer for database interactions
- **Controllers**: API endpoints handling HTTP requests
- **Models**: Domain entities representing core business objects

Each service is organized into specific operations (Find, Create, Update, etc.) following a clean separation of concerns.

## Key Features

- **User Management**: Registration, authentication, profile management
- **Tournament System**: Support for various competition formats (leagues, cups)
- **Prediction Engine**: Complex logic for guess validation and points calculation
- **Notification System**: Real-time updates for match results and predictions
- **Caching Layer**: Redis-based caching for performance optimization
- **Statistics**: Comprehensive user statistics and achievement tracking

## Development Setup

### Prerequisites

- PHP 8.2+
- MySQL 8.0.28+
- Redis
- Composer

### Local Development

1. **Database Setup**:
   ```
   MYSQL 8.0.28 start mysql server with mysql.server start connect mysql -u root -p errors files -u root -p -se
   "SHOW VARIABLES" | grep -e log_error -e general_log -e slow_query_log check status mysqladmin -u root -p status
   ```

2. **Web Server**:
   ```
   WEB SERVER - needs php8 start php server with – you need to cd public php -S localhost:8080 OR vscode debug
   ```

3. **Kill Existing Port**:
   ```
   lsof -ti :8080 | xargs kill -9
   ```

4. **Redis**:
   ```
   REDIS once you have redis installed, run redis-server
   ```

### Installation

1. Clone the repository
2. Install dependencies:
   ```
   composer install
   ```
3. Set up environment variables (copy .env.example to .env and configure)
4. Start the development server:
   ```
   cd public
   php -S localhost:8080
   ```

## API Documentation

The API provides endpoints for:

- Authentication (login, registration, token refresh)
- User management (profile, preferences)
- Tournaments and competitions
- Match data
- Predictions
- Statistics and leaderboards

## Project Structure

```
├── cli/                # CLI scripts for cron jobs
├── public/             # Public entry point
├── src/                # Application source code
│   ├── Controller/     # API endpoints
│   ├── Entity/         # Domain entities
│   ├── Repository/     # Data access layer
│   ├── Service/        # Business logic
│   │   ├── User/       # User-related services
│   │   ├── Match/      # Match-related services
│   │   ├── Guess/      # Prediction services
│   │   └── ...         # Other domain services
│   └── Middleware/     # Request/response middleware
└── tests/              # Test suite
```

## License

MIT

## Related Projects

- [nuxt-playpreso](https://github.com/pietropietro/nuxt-playpreso) - Frontend web application
- [nuxt-admin-playpreso](https://github.com/pietropietro/nuxt-admin-playpreso) - Admin dashboard

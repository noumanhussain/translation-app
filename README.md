# Translation Management System API

A robust API-driven translation management system built with Laravel, designed to handle large-scale translation operations with support for multiple languages, tags, and efficient querying.

## Features

-   🔐 JWT Authentication
-   🌐 Multi-language Support
-   🏷️ Translation Tagging System
-   📝 CRUD Operations for Translations
-   🔍 Efficient Search and Filtering
-   📊 Pagination Support
-   🚀 Scalable Design (100,000+ translations)
-   🐳 Docker Support

## Requirements

### Standard Setup

-   PHP 8.1 or higher
-   Composer
-   MySQL 5.7 or higher
-   Laravel 10.x

### Docker Setup

-   Docker
-   Docker Compose

## Installation

### Option 1: Standard Setup

1. Clone the repository:

```bash
git clone <repository-url>
cd Translation-App
```

2. Install dependencies:

```bash
composer install
```

3. Set up environment variables:

```bash
cp .env.example .env
```

Update the following variables in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

JWT_SECRET=your_jwt_secret
```

4. Generate application key and JWT secret:

```bash
php artisan key:generate
php artisan jwt:secret
```

5. Run migrations and seed the database:

```bash
php artisan migrate
php artisan db:seed
```

### Option 2: Docker Setup

1. Clone the repository:

```bash
git clone <repository-url>
cd Translation-App
```

2. Copy the environment file:

```bash
cp .env.example .env
```

3. Update the `.env` file with Docker-specific settings:

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=translation_db
DB_USERNAME=translation_user
DB_PASSWORD=translation_password

JWT_SECRET=your_jwt_secret
```

4. Build and start the Docker containers:

```bash
docker-compose up -d --build
```

5. Install dependencies:

```bash
docker-compose exec app composer install
```

6. Generate application key and JWT secret:

```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
```

7. Run migrations and seed the database:

```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

The application will be available at `http://localhost:8000`

### Docker Commands

```bash
# Start the application
docker-compose up -d

# Stop the application
docker-compose down

# View logs
docker-compose logs -f

# Access the PHP container
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan [command]

# Run composer commands
docker-compose exec app composer [command]
```

## API Documentation

### Authentication Endpoints

#### Register a New User

```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login

```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Other Auth Endpoints

-   `POST /api/auth/logout` - Logout user
-   `POST /api/auth/refresh` - Refresh JWT token
-   `GET /api/auth/profile` - Get user profile

### Translation Endpoints

All translation endpoints require authentication. Include the JWT token in the Authorization header:

```
Authorization: Bearer <your_jwt_token>
```

#### Languages

-   `GET /api/v1/languages` - List all languages
-   `POST /api/v1/languages` - Create a new language
-   `GET /api/v1/languages/{id}` - Get language details
-   `PUT /api/v1/languages/{id}` - Update language
-   `DELETE /api/v1/languages/{id}` - Delete language

#### Translations

-   `GET /api/v1/translations` - List translations (paginated)
-   `POST /api/v1/translations` - Create translation
-   `GET /api/v1/translations/{id}` - Get translation details
-   `PUT /api/v1/translations/{id}` - Update translation
-   `DELETE /api/v1/translations/{id}` - Delete translation
-   `GET /api/v1/translations/by-key` - Search translations by key

Query Parameters for Translations:

-   `per_page` (default: 50, max: 100)
-   `page` (default: 1)
-   `language` (filter by language code)
-   `group` (filter by group)
-   `tag` (filter by tag name)
-   `key` (search in translation keys)

## Architecture and Design Choices

### Database Schema

1. **Languages Table**

    - Stores supported languages
    - Fields: id, code, name, is_active

2. **Translations Table**

    - Stores translation entries
    - Fields: id, key, value, language_id, group
    - Indexed for efficient querying
    - No unique constraint on key to allow same key across languages

3. **Tags Table**

    - Provides context and categorization
    - Fields: id, name, description

4. **Tag Translation Pivot Table**
    - Many-to-many relationship between tags and translations
    - Fields: tag_id, translation_id

### Design Decisions

1. **JWT Authentication**

    - Stateless authentication for API scalability
    - Token refresh mechanism for security
    - Bearer token implementation for standard API auth

2. **Pagination Implementation**

    - Default 50 items per page
    - Maximum 100 items per page to prevent server overload
    - Metadata included in response for client-side handling

3. **Query Optimization**

    - Selective loading of relationships
    - Efficient eager loading
    - Indexed fields for common queries
    - Chunked seeding for large datasets

4. **PSR-12 Compliance**

    - Strict typing enabled
    - Consistent code formatting
    - Clear class and method organization
    - Proper documentation blocks

5. **Security Measures**
    - Password hashing
    - Input validation
    - Protected routes
    - Rate limiting capability

## Testing

Run the test suite:

```bash
php artisan test
```

## Performance Considerations

-   Database indexes on frequently queried fields
-   Chunked processing for large datasets
-   Efficient relationship loading
-   Query optimization for common operations
-   Scalable seeding process for 100,000+ records

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

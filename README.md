# Company Manager Application

## Overview
The Company Manager is a web-based application that allows users to manage Danish companies using their CVR (Central Business Register) numbers. The application provides functionality to add, view, update, and delete company information, with automatic synchronization with the Danish CVR API.

## Website
The application has been deployed on the **https://git.dk/** site.

## Features
- Add companies using CVR numbers
- Automatic data synchronization with CVRAPI.dk
- Real-time search and filtering capabilities
- Sort companies by various fields
- Responsive design for all devices
- Error handling and user feedback
- SQLite database storage

## Prerequisites
- PHP 8.0 or higher
- SQLite3
- PHP Extensions:
  - PDO
  - SQLite
  - cURL
  - OpenSSL
- Web browser with JavaScript enabled

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/company-manager.git
cd company-manager
```

2. Verify PHP installation and extensions:
```bash
php -v
php -m | grep -E "pdo|sqlite|curl|openssl"
```

3. Create database:
```bash
php db/create_database.php
```

4. Start the PHP development server:
```bash
php -S localhost:8000
```

5. Access the application:
```
http://localhost:8000
```

## Project Structure
```
/company-manager/
├── api/                  # API endpoints
│   ├── companies.php     # Company CRUD operations
│   └── sync.php         # CVR API sync
├── app/                  # Application logic
│   ├── config.php       # Configuration
│   └── company.php      # Company class
├── assets/              # Frontend assets
│   ├── css/
│   │   └── styles.css
│   └── js/
│       └── main.js
├── data/                # Database
│   └── companies.db
├── db/                  # Database scripts
│   ├── create_database.php
│   └── schema.sql
├── tests/               # Test files
│   ├── test_api.php
│   └── test_database.php
└── index.php           # Main entry point
```

## Usage

### Adding a Company
1. Enter an 8-digit CVR number in the input field
2. Click "Add Company" or press Enter
3. The company information will be automatically fetched and displayed

### Searching Companies
1. Use the search bar to filter companies
2. Select specific fields to search in
3. Sort results by name, CVR, or date
4. Toggle ascending/descending order

### Syncing Company Data
1. Click the "Sync" button on any company card
2. Latest data will be fetched from CVRAPI.dk
3. Company information will be updated automatically

## Testing

### API Testing
```bash
cd tests
php test_api.php
```

### Database Testing
```bash
cd tests
php test_database.php
```

### Manual Testing
1. Start the development server
2. Add test companies using provided CVR numbers
3. Test search and filter functionality
4. Verify sync operations

## API Documentation

### Endpoints

#### GET /api/companies.php
- Returns list of all companies
- Response: `{ "success": true, "data": [...] }`

#### POST /api/companies.php
- Creates new company
- Body: `{ "cvr_number": "12345678" }`
- Response: `{ "success": true, "message": "..." }`

#### DELETE /api/companies.php?id={id}
- Deletes company
- Response: `{ "success": true, "message": "..." }`

#### POST /api/sync.php?id={id}
- Syncs company data with CVR API
- Response: `{ "success": true, "message": "..." }`

## Error Handling
- Input validation errors
- API connection errors
- Database errors
- Network errors
- Invalid CVR numbers

## Development

### Debug Mode
Set `DEBUG` constant in config.php:
```php
define('DEVELOPMENT', true);
```

### Adding Features
1. Create feature branch
2. Implement changes
3. Add tests
4. Submit pull request

## Troubleshooting

### Common Issues
1. Database connection errors
   - Check SQLite file permissions
   - Verify PHP SQLite extension

2. API sync failures
   - Check internet connection
   - Verify CVR API access
   - Check SSL certificates

3. Search not working
   - Clear browser cache
   - Check JavaScript console
   - Verify data loading

## Security Considerations
- SQL injection prevention using prepared statements
- Input sanitization
- API request validation
- Error message sanitization
- SSL verification for API calls

## Performance
- Client-side filtering for fast search
- Minimized API calls
- Database indexing
- Response caching
- Optimized queries

## Contributing
1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create pull request

## Contact
Lasse Fink Hansen

Phone: +45 30950087

Email: lassefink3@gmail.com


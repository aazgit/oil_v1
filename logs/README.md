# KishansKraft Application Logs

This directory contains application log files. Log files are automatically created and rotated by the application.

## Log Files

- `app.log` - General application events and information
- `error.log` - Error messages and exceptions
- `security.log` - Security-related events (login attempts, rate limiting, etc.)
- `api.log` - API request and response logs
- `database.log` - Database query logs (when enabled)

## Log Levels

- **DEBUG** - Detailed debugging information
- **INFO** - General information messages
- **WARNING** - Warning messages
- **ERROR** - Error conditions
- **CRITICAL** - Critical error conditions

## Log Rotation

Logs are automatically rotated when they exceed 10MB in size. Up to 5 historical log files are maintained.

## Security

Log files may contain sensitive information. Ensure proper file permissions and access controls are in place.

**Never commit log files to version control systems.**

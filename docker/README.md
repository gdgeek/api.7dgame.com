# Docker Local Development Setup

## Quick Start

1. **Copy environment file**
   ```bash
   cp .env.docker.example .env.docker
   ```

2. **Edit `.env.docker` with your actual credentials**
   - Update database passwords
   - Add your email credentials for testing email features
   - Add Tencent Cloud credentials if testing file uploads
   - Add WeChat credentials if testing WeChat integration

3. **Generate JWT key (if not exists)**
   ```bash
   mkdir -p jwt_keys
   openssl ecparam -genkey -name prime256v1 -noout -out jwt_keys/jwt-key.pem
   ```

4. **Build and start containers**
   ```bash
   docker-compose up -d
   ```

5. **Run database migrations**
   ```bash
   docker-compose exec api php yii migrate --interactive=0
   ```

6. **Initialize RBAC**
   ```bash
   docker-compose exec api php yii rbac/init
   ```

## Services

- **API**: http://localhost:8081 - Main API service
- **Backend**: http://localhost:8082 - Backend application
- **phpMyAdmin**: http://localhost:8080 - Database management
- **MySQL**: localhost:3306 - Database server
- **Redis**: localhost:6379 - Cache server

## Useful Commands

### View logs
```bash
docker-compose logs -f api
docker-compose logs -f app
```

### Access container shell
```bash
docker-compose exec api bash
docker-compose exec app bash
```

### Run Yii commands
```bash
docker-compose exec api php yii <command>
```

### Stop services
```bash
docker-compose down
```

### Stop and remove volumes (WARNING: deletes all data)
```bash
docker-compose down -v
```

## Security Notes

- **Never commit** `.env.docker` to version control
- Use strong passwords for production environments
- The default passwords are for local development only
- JWT keys should be generated uniquely for each environment

## Troubleshooting

### Port conflicts
If ports 8080, 8081, 8082, 3306, or 6379 are already in use, edit `docker-compose.yml` to use different ports.

### Permission issues
If you encounter permission issues with volumes:
```bash
docker-compose exec api chown -R www-data:www-data /var/www/html/advanced/runtime
docker-compose exec api chmod -R 777 /var/www/html/advanced/runtime
```

### Database connection issues
Ensure the database service is fully started before running migrations:
```bash
docker-compose logs db
```

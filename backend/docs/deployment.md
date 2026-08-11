# Deployment Guide — INNOW Digital Attendance System

## Deployment Scenarios

The INNOW system supports two deployment models:

1. **Local / Offline** — runs on the client's local network (XAMPP, no internet required)
2. **Hosted (e.g., Xneelo)** — runs on a VPS with a public domain

## Option 1: Local / Offline Deployment

### Hardware Requirements
- A Windows PC or server on the local network
- Minimum 4GB RAM, 2 CPU cores
- 20GB free disk space

### Steps

1. **Install XAMPP** on the server machine
   - Enable Apache and MySQL modules
   - Ensure ports 80 and 3306 are available

2. **Place the project** in `C:\xampp\htdocs\innow-attendance`

3. **Import the database**
   ```bash
   mysql -u root -pKwaNomaLiv24! innow_db < database.sql
   ```

4. **Configure `.env`**
   - Project root `.env`: set `APP_URL=http://<server-ip>`
   - Backend `.env`: set `APP_DEBUG=false` for production

5. **Start Apache** from the XAMPP Control Panel

6. **Access** from any device on the network: `http://<server-ip>/innow-attendance/frontend/public/index.php`

> **Note:** The PHP built-in server (`php -S`) is only for development. For shared office use, use Apache via XAMPP.

### Firewall Configuration

If staff cannot access the server:
1. Open **Windows Defender Firewall**
2. Allow inbound connections on:
   - Port **80** (HTTP)
   - Port **443** (HTTPS, if configured)
   - Port **3306** (MySQL, only if remote DB access is needed)
3. Ensure all staff devices are on the same subnet

## Option 2: Hosted Deployment (e.g., Xneelo VPS)

### Prerequisites
- SSH access to the VPS
- Domain name pointed to the VPS IP
- Composer installed

### Steps

1. **SSH into the VPS**
   ```bash
   ssh root@your-vps-ip
   ```

2. **Install LAMP stack**
   ```bash
   apt update && apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-cli unzip -y
   mysql_secure_installation
   ```

3. **Create the database**
   ```bash
   mysql -u root -p
   CREATE DATABASE innow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'innow_user'@'localhost' IDENTIFIED BY 'StrongPasswordHere!';
   GRANT ALL PRIVILEGES ON innow_db.* TO 'innow_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

4. **Upload the project**
   ```bash
   cd /var/www/html
   git clone <your-repo-url> innow-attendance
   cd innow-attendance
   ```

5. **Install dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

6. **Configure environment**
   ```bash
   cp .env.example .env   # if you create one, otherwise edit directly
   nano .env
   # Set DB credentials, APP_ENV=production, APP_DEBUG=false
   nano backend/.env
   # Set same DB credentials, APP_SECRET to a random 32-char string
   ```

7. **Import the database**
   ```bash
   mysql -u innow_user -p innow_db < database.sql
   ```

8. **Set permissions**
   ```bash
   chown -R www-data:www-data /var/www/html/innow-attendance
   chmod -R 755 /var/www/html/innow-attendance
   ```

9. **Configure Apache Virtual Host** (optional, for clean URLs)
   ```apache
   <VirtualHost *:80>
       ServerName attendance.yourdomain.co.za
       DocumentRoot /var/www/html/innow-attendance/frontend/public
       <Directory /var/www/html/innow-attendance/frontend/public>
           AllowOverride All
           Require all granted
       </Directory>
       ErrorLog ${APACHE_LOG_DIR}/innow-error.log
       CustomLog ${APACHE_LOG_DIR}/innow-access.log combined
   </VirtualHost>
   ```

10. **Enable the site and restart Apache**
    ```bash
    a2ensite innow-attendance
    systemctl reload apache2
    ```

## Data Backup and Recovery

### Manual Backup (mysqldump)

```bash
mysqldump -u innow_user -p innow_db > backup_$(date +%Y%m%d).sql
```

Store the backup on a separate drive or cloud storage.

### Automated Backup (cron)

Edit the crontab:
```bash
crontab -e
```

Add a daily 2AM backup:
```bash
0 2 * * * /usr/bin/mysqldump -u innow_user -pStrongPasswordHere! innow_db > /home/backups/innow_backup_$(date +\%Y\%m\%d).sql
```

### Recovery

To restore from a backup:
```bash
mysql -u innow_user -p innow_db < backup_20260729.sql
```

## Monitoring

- Check Apache error log: `/var/www/html/innow-attendance/backend/logs/` (if configured)
- Check MySQL slow query log for performance tuning
- Monitor disk space for the backup folder

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_SECRET` (random 32+ character string)
- [ ] Strong database password (not the default)
- [ ] Firewall rules restrict port 3306 to localhost only
- [ ] HTTPS configured (Let's Encrypt / Certbot)
- [ ] Regular OS and package updates
- [ ] Daily database backups stored off-server

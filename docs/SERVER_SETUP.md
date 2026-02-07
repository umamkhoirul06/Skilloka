# =============================================================================
# Skilloka - Server Setup Guide
# Instructions for setting up production/staging servers
# =============================================================================

## Prerequisites

- Ubuntu 22.04 LTS or later
- Minimum 2 vCPU, 4GB RAM, 50GB SSD
- Root access or sudo privileges
- Domain name configured with Cloudflare DNS

---

## Initial Server Setup

### 1. Update System

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git vim htop ufw fail2ban
```

### 2. Create Deploy User

```bash
# Create user
sudo useradd -m -s /bin/bash deploy
sudo passwd deploy

# Add to sudo group (optional, for initial setup)
sudo usermod -aG sudo deploy

# Setup SSH
sudo mkdir -p /home/deploy/.ssh
sudo chmod 700 /home/deploy/.ssh
sudo touch /home/deploy/.ssh/authorized_keys
sudo chmod 600 /home/deploy/.ssh/authorized_keys
sudo chown -R deploy:deploy /home/deploy/.ssh

# Add your public key
echo "your-public-key" | sudo tee -a /home/deploy/.ssh/authorized_keys
```

### 3. Configure SSH Security

```bash
# Edit SSH config
sudo vim /etc/ssh/sshd_config

# Set these values:
# PermitRootLogin no
# PasswordAuthentication no
# PubkeyAuthentication yes
# AuthorizedKeysFile .ssh/authorized_keys

# Restart SSH
sudo systemctl restart sshd
```

### 4. Configure Firewall

```bash
# Enable UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
sudo ufw status
```

### 5. Configure Fail2ban

```bash
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo vim /etc/fail2ban/jail.local

# Add/modify:
# [sshd]
# enabled = true
# port = 22
# maxretry = 5
# bantime = 3600

sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## Install Docker

```bash
# Install Docker
curl -fsSL https://get.docker.com | sh

# Add deploy user to docker group
sudo usermod -aG docker deploy

# Start Docker
sudo systemctl enable docker
sudo systemctl start docker

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify
docker --version
docker-compose --version
```

---

## Application Directory Setup

```bash
# Create directories
sudo mkdir -p /opt/skilloka
sudo mkdir -p /backups/postgres
sudo mkdir -p /var/log/skilloka

# Set ownership
sudo chown -R deploy:deploy /opt/skilloka
sudo chown -R deploy:deploy /backups
sudo chown -R deploy:deploy /var/log/skilloka

# Clone project (first deployment)
cd /opt/skilloka
git clone https://github.com/your-org/skilloka.git .
```

---

## SSL Certificate Setup (Cloudflare)

### Option 1: Cloudflare Origin Certificates (Recommended)

1. Go to Cloudflare Dashboard > SSL/TLS > Origin Server
2. Create Certificate
3. Save certificate and key:

```bash
# Create SSL directory
sudo mkdir -p /opt/skilloka/docker/ssl

# Save certificate
sudo vim /opt/skilloka/docker/ssl/fullchain.pem
# Paste certificate content

# Save private key
sudo vim /opt/skilloka/docker/ssl/privkey.pem
# Paste private key content

# Set permissions
sudo chmod 600 /opt/skilloka/docker/ssl/*.pem
sudo chown deploy:deploy /opt/skilloka/docker/ssl/*.pem
```

### Option 2: Let's Encrypt

```bash
# Install certbot
sudo apt install -y certbot python3-certbot-dns-cloudflare

# Create Cloudflare credentials
sudo mkdir -p /root/.cloudflare
sudo vim /root/.cloudflare/credentials
# dns_cloudflare_api_token = your-api-token

sudo chmod 600 /root/.cloudflare/credentials

# Get certificate
sudo certbot certonly \
    --dns-cloudflare \
    --dns-cloudflare-credentials /root/.cloudflare/credentials \
    -d api.skilloka.id \
    -d *.api.skilloka.id

# Copy to project
sudo cp /etc/letsencrypt/live/api.skilloka.id/fullchain.pem /opt/skilloka/docker/ssl/
sudo cp /etc/letsencrypt/live/api.skilloka.id/privkey.pem /opt/skilloka/docker/ssl/
```

---

## Environment Configuration

```bash
# Copy environment template
cp /opt/skilloka/.env.production.example /opt/skilloka/.env.production

# Edit with actual values
vim /opt/skilloka/.env.production

# Key values to configure:
# - APP_KEY (generate with: php artisan key:generate --show)
# - DB_PASSWORD
# - REDIS_PASSWORD
# - All API keys and secrets
```

---

## Managed Database Connection (Optional)

If using managed PostgreSQL (RDS, DigitalOcean, etc.):

```bash
# Update .env.production
DB_HOST=your-managed-db-host.com
DB_PORT=5432
DB_DATABASE=skilloka
DB_USERNAME=skilloka
DB_PASSWORD=your-secure-password

# For SSL connections
DB_SSLMODE=require
```

---

## Initial Deployment

```bash
# Switch to deploy user
su - deploy
cd /opt/skilloka

# Pull Docker images
docker-compose -f docker-compose.prod.yml pull

# Start services
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose -f docker-compose.prod.yml exec backend php artisan migrate --force

# Seed initial data (if needed)
docker-compose -f docker-compose.prod.yml exec backend php artisan db:seed --force

# Generate app key (if not set)
docker-compose -f docker-compose.prod.yml exec backend php artisan key:generate

# Optimize
docker-compose -f docker-compose.prod.yml exec backend php artisan optimize

# Verify
curl -f http://localhost/health
```

---

## Cron Jobs Setup

```bash
# Database backup cron
sudo crontab -u deploy -e

# Add these lines:
# Daily database backup at 2 AM
0 2 * * * /opt/skilloka/scripts/backup-database.sh >> /var/log/skilloka/backup.log 2>&1

# SSL certificate renewal check (weekly)
0 3 * * 0 /opt/skilloka/scripts/ssl-renew.sh renew >> /var/log/skilloka/ssl.log 2>&1

# Log rotation (daily)
0 4 * * * docker system prune -f >> /var/log/skilloka/docker-cleanup.log 2>&1
```

---

## Monitoring Setup

### Install Node Exporter (for Prometheus)

```bash
# Create user
sudo useradd --no-create-home --shell /bin/false node_exporter

# Download
wget https://github.com/prometheus/node_exporter/releases/download/v1.7.0/node_exporter-1.7.0.linux-amd64.tar.gz
tar xvf node_exporter-1.7.0.linux-amd64.tar.gz
sudo mv node_exporter-1.7.0.linux-amd64/node_exporter /usr/local/bin/
sudo chown node_exporter:node_exporter /usr/local/bin/node_exporter

# Create service
sudo vim /etc/systemd/system/node_exporter.service
```

```ini
[Unit]
Description=Node Exporter
Wants=network-online.target
After=network-online.target

[Service]
User=node_exporter
Group=node_exporter
Type=simple
ExecStart=/usr/local/bin/node_exporter

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable node_exporter
sudo systemctl start node_exporter
```

---

## Unattended Upgrades (Security Patches)

```bash
sudo apt install -y unattended-upgrades apt-listchanges

sudo dpkg-reconfigure -plow unattended-upgrades

# Configure
sudo vim /etc/apt/apt.conf.d/50unattended-upgrades

# Enable security updates
# Unattended-Upgrade::Allowed-Origins {
#     "${distro_id}:${distro_codename}-security";
# };
```

---

## Log Rotation

```bash
sudo vim /etc/logrotate.d/skilloka
```

```
/var/log/skilloka/*.log {
    daily
    rotate 14
    compress
    delaycompress
    missingok
    notifempty
    create 0640 deploy deploy
}
```

---

## Verification Checklist

- [ ] SSH access works with key authentication
- [ ] Firewall is enabled (UFW)
- [ ] Docker is running
- [ ] Application containers are healthy
- [ ] Database connection works
- [ ] SSL certificate is valid
- [ ] Health endpoint responds
- [ ] Backup cron is scheduled
- [ ] Monitoring agents are running

---

*Last Updated: 2026-02-07*

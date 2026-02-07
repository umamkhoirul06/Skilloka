# =============================================================================
# SKILLOKA OPERATIONS RUNBOOK
# Comprehensive guide for managing the Skilloka production environment
# =============================================================================

## Table of Contents
1. [System Overview](#system-overview)
2. [Common Operations](#common-operations)
3. [Troubleshooting Guide](#troubleshooting-guide)
4. [Emergency Procedures](#emergency-procedures)
5. [Monitoring & Alerts](#monitoring--alerts)
6. [Backup & Recovery](#backup--recovery)
7. [Security Procedures](#security-procedures)

---

## System Overview

### Architecture
```
                    ┌─────────────────────┐
                    │    Cloudflare CDN   │
                    │   (DNS + SSL + WAF) │
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │       Nginx         │
                    │   (Reverse Proxy)   │
                    └──────────┬──────────┘
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
┌─────────▼─────────┐ ┌────────▼────────┐ ┌────────▼────────┐
│    PHP-FPM        │ │    Horizon      │ │    Scheduler    │
│   (Backend API)   │ │  (Queue Worker) │ │     (Cron)      │
└─────────┬─────────┘ └────────┬────────┘ └────────┬────────┘
          │                    │                    │
          └────────────────────┼────────────────────┘
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
┌─────────▼─────────┐ ┌────────▼────────┐ ┌────────▼────────┐
│    PostgreSQL     │ │      Redis      │ │       MinIO     │
│    (Database)     │ │  (Cache/Queue)  │ │    (Storage)    │
└───────────────────┘ └─────────────────┘ └─────────────────┘
```

### Key Services
| Service | Port | Description |
|---------|------|-------------|
| Nginx | 80, 443 | Reverse proxy, SSL termination |
| PHP-FPM | 9000 | Backend API application |
| PostgreSQL | 5432 | Primary database |
| Redis | 6379 | Cache and queue backend |
| Horizon | - | Laravel queue manager |
| Prometheus | 9090 | Metrics collection |
| Grafana | 3000 | Monitoring dashboards |

### Important Paths
| Path | Description |
|------|-------------|
| `/opt/skilloka` | Application root |
| `/opt/skilloka/backend` | Laravel backend |
| `/opt/skilloka/scripts` | Deployment scripts |
| `/backups/postgres` | Database backups |
| `/var/log/nginx` | Nginx logs |
| `/var/log/php` | PHP-FPM logs |

---

## Common Operations

### 1. Deploying a New Version

**Automatic (via GitHub Actions)**
```bash
# Push to develop branch for staging
git push origin develop

# Push to main branch for production (requires approval)
git push origin main
```

**Manual Deployment**
```bash
cd /opt/skilloka
./scripts/deploy.sh deploy
```

### 2. Viewing Application Logs

```bash
# All logs
docker compose -f docker-compose.prod.yml logs -f

# Specific service
docker compose -f docker-compose.prod.yml logs -f backend

# Tail last 100 lines
docker compose -f docker-compose.prod.yml logs --tail=100 backend

# Laravel logs
docker compose exec backend tail -f storage/logs/laravel.log
```

### 3. Running Artisan Commands

```bash
# General artisan command
docker compose -f docker-compose.prod.yml exec backend php artisan <command>

# Clear all caches
docker compose exec backend php artisan optimize:clear

# Run migrations
docker compose exec backend php artisan migrate --force

# Check queue status
docker compose exec backend php artisan horizon:status
```

### 4. Database Operations

```bash
# Access PostgreSQL CLI
docker exec -it skilloka-postgres psql -U skilloka -d skilloka

# Run a query
docker exec skilloka-postgres psql -U skilloka -d skilloka -c "SELECT * FROM users LIMIT 5;"

# Export data
docker exec skilloka-postgres pg_dump -U skilloka skilloka > backup.sql
```

### 5. Redis Operations

```bash
# Access Redis CLI
docker exec -it skilloka-redis redis-cli -a $REDIS_PASSWORD

# Clear cache
docker compose exec backend php artisan cache:clear

# Monitor Redis
docker exec skilloka-redis redis-cli -a $REDIS_PASSWORD MONITOR
```

### 6. Container Management

```bash
# View all containers
docker compose -f docker-compose.prod.yml ps

# Restart specific service
docker compose -f docker-compose.prod.yml restart backend

# View container stats
docker stats

# Enter container shell
docker compose exec backend sh
```

---

## Troubleshooting Guide

### Issue: 502 Bad Gateway

**Symptoms**: Users see 502 errors

**Diagnosis**:
```bash
# Check if backend is running
docker compose ps backend

# Check PHP-FPM logs
docker compose logs --tail=50 backend

# Check nginx error log
docker compose logs --tail=50 nginx
```

**Resolution**:
1. Restart PHP-FPM: `docker compose restart backend`
2. Check for OOM kills: `dmesg | grep -i kill`
3. Verify PHP-FPM configuration
4. Check application for fatal errors

### Issue: High Response Times

**Symptoms**: API requests taking > 2 seconds

**Diagnosis**:
```bash
# Check database slow queries
docker exec skilloka-postgres psql -U skilloka -d skilloka \
  -c "SELECT query, calls, mean_time FROM pg_stat_statements ORDER BY mean_time DESC LIMIT 10;"

# Check Redis latency
docker exec skilloka-redis redis-cli -a $REDIS_PASSWORD DEBUG SLEEP 0.1

# Check PHP-FPM status
curl http://localhost/fpm-status
```

**Resolution**:
1. Optimize slow database queries
2. Add missing indexes
3. Increase PHP-FPM workers
4. Clear and warm caches
5. Scale horizontally if needed

### Issue: Queue Jobs Failing

**Symptoms**: Jobs stuck in failed_jobs table

**Diagnosis**:
```bash
# Check Horizon status
docker compose exec backend php artisan horizon:status

# View failed jobs
docker compose exec backend php artisan queue:failed

# Check Horizon logs
docker compose logs horizon
```

**Resolution**:
1. Review job exception in failed_jobs table
2. Fix the underlying issue
3. Retry failed jobs: `php artisan queue:retry all`
4. Restart Horizon: `php artisan horizon:terminate`

### Issue: Database Connection Refused

**Symptoms**: Laravel shows "Connection refused" errors

**Diagnosis**:
```bash
# Check PostgreSQL status
docker compose ps postgres

# Check PostgreSQL logs
docker compose logs postgres

# Test connection
docker exec skilloka-backend pg_isready -h postgres -U skilloka
```

**Resolution**:
1. Restart PostgreSQL: `docker compose restart postgres`
2. Check disk space: `df -h`
3. Verify connection limits not exceeded
4. Check pg_hba.conf for access rules

### Issue: Out of Disk Space

**Symptoms**: Application errors, backup failures

**Diagnosis**:
```bash
# Check disk usage
df -h

# Find large files
du -sh /var/lib/docker/*
du -sh /opt/skilloka/*

# Check Docker disk usage
docker system df
```

**Resolution**:
1. Clean Docker: `docker system prune -af`
2. Remove old backups: `find /backups -mtime +30 -delete`
3. Rotate logs: `logrotate -f /etc/logrotate.conf`
4. Increase disk size if needed

### Issue: Redis Out of Memory

**Symptoms**: Redis returning OOM errors

**Diagnosis**:
```bash
# Check Redis memory
docker exec skilloka-redis redis-cli -a $REDIS_PASSWORD INFO memory

# Check key sizes
docker exec skilloka-redis redis-cli -a $REDIS_PASSWORD DEBUG OBJECT <key>
```

**Resolution**:
1. Clear unnecessary caches: `php artisan cache:clear`
2. Reduce TTL for cached items
3. Increase Redis maxmemory
4. Enable key eviction: `CONFIG SET maxmemory-policy allkeys-lru`

---

## Emergency Procedures

### Complete System Failure

1. **Assess the situation**
   ```bash
   docker ps -a
   systemctl status docker
   dmesg | tail -50
   ```

2. **Restart all services**
   ```bash
   cd /opt/skilloka
   docker compose -f docker-compose.prod.yml down
   docker compose -f docker-compose.prod.yml up -d
   ```

3. **If services won't start, check resources**
   ```bash
   free -h
   df -h
   top
   ```

4. **Escalation path**
   - Level 1: On-call DevOps (PagerDuty)
   - Level 2: Infrastructure Lead
   - Level 3: CTO

### Rollback Procedure

```bash
cd /opt/skilloka

# Check previous version
cat .previous-deployment

# Execute rollback
./scripts/deploy.sh rollback

# Verify rollback
curl -f https://api.skilloka.id/health
```

### Database Corruption

1. **Stop all writes**
   ```bash
   docker compose stop backend horizon scheduler
   ```

2. **Assess damage**
   ```bash
   docker exec skilloka-postgres psql -U skilloka -d skilloka \
     -c "SELECT datname, pg_database_size(datname) FROM pg_database;"
   ```

3. **Restore from backup**
   ```bash
   ./scripts/restore-database.sh /backups/postgres/latest.sql.gz --confirm
   ```

4. **Resume services**
   ```bash
   docker compose start backend horizon scheduler
   ```

### Security Incident

1. **Immediate actions**
   - Block suspicious IPs at Cloudflare
   - Rotate affected credentials
   - Preserve logs for forensics

2. **Containment**
   ```bash
   # Block IP at firewall
   ufw deny from <suspicious_ip>
   
   # Disable compromised user
   docker compose exec backend php artisan user:disable <user_id>
   ```

3. **Investigation**
   - Review access logs
   - Check for unauthorized changes
   - Audit database modifications

4. **Recovery**
   - Restore from known-good backup if needed
   - Rotate all secrets
   - Update security patches

---

## Monitoring & Alerts

### Key Metrics to Monitor

| Metric | Warning Threshold | Critical Threshold |
|--------|-------------------|-------------------|
| CPU Usage | > 80% | > 95% |
| Memory Usage | > 80% | > 95% |
| Disk Usage | > 80% | > 95% |
| Error Rate | > 5% | > 10% |
| Response Time (p95) | > 2s | > 5s |
| Queue Depth | > 1000 | > 5000 |

### Alert Response

1. **Warning alerts**: Investigate within 1 hour
2. **Critical alerts**: Investigate immediately
3. **Page-level alerts**: Wake on-call engineer

### Grafana Dashboards

| Dashboard | URL | Description |
|-----------|-----|-------------|
| Overview | /d/overview | System health summary |
| Backend | /d/backend | PHP-FPM metrics |
| Database | /d/database | PostgreSQL metrics |
| Redis | /d/redis | Cache and queue metrics |
| Nginx | /d/nginx | Request metrics |

---

## Backup & Recovery

### Backup Schedule

| Type | Frequency | Retention | Location |
|------|-----------|-----------|----------|
| Database | Daily | 30 days | S3 + Local |
| Redis | Hourly | 7 days | Local |
| Files | Daily | 30 days | S3 |

### Manual Backup

```bash
# Database backup
./scripts/backup-database.sh

# Full system backup
tar -czvf /backups/skilloka-$(date +%Y%m%d).tar.gz \
  --exclude='/opt/skilloka/vendor' \
  --exclude='/opt/skilloka/node_modules' \
  /opt/skilloka
```

### Restore Procedures

```bash
# Database restore
./scripts/restore-database.sh /backups/postgres/skilloka_20240101_120000.sql.gz

# Application restore
cd /opt/skilloka
tar -xzvf /backups/skilloka-20240101.tar.gz
docker compose up -d
php artisan migrate --force
```

---

## Security Procedures

### SSL Certificate Renewal

Certificates auto-renew via Let's Encrypt. Manual renewal:
```bash
./scripts/ssl-renew.sh renew
```

### Secret Rotation

1. **Database password**
   ```bash
   # Update in .env.production
   # Update PostgreSQL
   docker exec skilloka-postgres psql -U postgres \
     -c "ALTER USER skilloka WITH PASSWORD 'new_password';"
   # Restart services
   docker compose restart
   ```

2. **API keys**
   - Update in `.env.production`
   - Restart affected services
   - Update external service configurations

### Security Updates

```bash
# Update base images
docker compose pull
docker compose up -d

# Apply host OS updates
apt update && apt upgrade -y
# Reboot if kernel update
```

---

## Contact Information

| Role | Name | Contact |
|------|------|---------|
| On-Call DevOps | PagerDuty | @devops-oncall |
| Infrastructure Lead | - | - |
| Security Team | - | security@skilloka.id |
| Database Admin | - | - |

---

*Last Updated: 2026-02-07*
*Version: 1.0.0*

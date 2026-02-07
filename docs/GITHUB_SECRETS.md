# =============================================================================
# Skilloka - GitHub Repository Secrets Setup Guide
# Configure these secrets in GitHub: Settings > Secrets and variables > Actions
# =============================================================================

## Required Secrets

### Docker Registry
| Secret Name | Description | Example |
|-------------|-------------|---------|
| `DOCKER_REGISTRY` | Container registry URL | `ghcr.io/your-org` or `your-registry.io` |
| `DOCKER_USERNAME` | Registry username | `github_username` |
| `DOCKER_PASSWORD` | Registry password/token | `ghp_xxxx` or PAT |

### Server Access
| Secret Name | Description | Example |
|-------------|-------------|---------|
| `STAGING_HOST` | Staging server IP/hostname | `staging.skilloka.id` |
| `STAGING_USER` | SSH username for staging | `deploy` |
| `STAGING_SSH_KEY` | Private SSH key for staging | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `PRODUCTION_HOST` | Production server IP/hostname | `api.skilloka.id` |
| `PRODUCTION_USER` | SSH username for production | `deploy` |
| `PRODUCTION_SSH_KEY` | Private SSH key for production | `-----BEGIN OPENSSH PRIVATE KEY-----...` |

### Monitoring & Notifications
| Secret Name | Description | Example |
|-------------|-------------|---------|
| `SENTRY_AUTH_TOKEN` | Sentry authentication token | `sntrys_xxx` |
| `SENTRY_ORG` | Sentry organization slug | `skilloka` |
| `SLACK_WEBHOOK` | Slack webhook URL for notifications | `https://hooks.slack.com/services/xxx` |
| `CODECOV_TOKEN` | Codecov token for coverage reports | `xxxxx` |

### Status Page (Optional)
| Secret Name | Description | Example |
|-------------|-------------|---------|
| `STATUSPAGE_ID` | Statuspage.io page ID | `xxxxx` |
| `STATUSPAGE_API_KEY` | Statuspage API key | `xxxxx` |

---

## Environment Variables

### CI Pipeline
The following are set in the workflow files, not as secrets:
- `PHP_VERSION`: 8.2
- `NODE_VERSION`: 20
- `FLUTTER_VERSION`: 3.27.2

---

## Setup Instructions

### 1. Docker Registry (GitHub Container Registry)

```bash
# Create a Personal Access Token with packages:write permission
# Go to: Settings > Developer settings > Personal access tokens > Tokens (classic)
# Select scopes: write:packages, read:packages, delete:packages

# Save as DOCKER_PASSWORD secret
# Use your GitHub username as DOCKER_USERNAME
# Use ghcr.io/your-username as DOCKER_REGISTRY
```

### 2. SSH Keys for Deployment

```bash
# Generate a deployment key pair
ssh-keygen -t ed25519 -C "deploy@skilloka" -f skilloka_deploy

# Add public key to server's authorized_keys
cat skilloka_deploy.pub >> ~/.ssh/authorized_keys

# Add private key as STAGING_SSH_KEY or PRODUCTION_SSH_KEY secret
cat skilloka_deploy
```

### 3. Sentry Setup

```bash
# Create auth token at: https://sentry.io/settings/account/api/auth-tokens/
# Required scopes: project:releases, project:read, org:read

# Find your org slug at: https://sentry.io/settings/
```

### 4. Slack Webhook

```bash
# Create incoming webhook at: https://api.slack.com/apps
# 1. Create new app
# 2. Go to Incoming Webhooks
# 3. Activate and create webhook for #deployments channel
```

### 5. Codecov

```bash
# Get token at: https://codecov.io/gh/your-org/skilloka
# Add repository and copy the upload token
```

---

## Server Preparation

### Create deploy user on servers

```bash
# On staging/production server
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG docker deploy

# Create SSH directory
sudo mkdir -p /home/deploy/.ssh
sudo chmod 700 /home/deploy/.ssh

# Add deployment public key
echo "ssh-ed25519 AAAA... deploy@skilloka" | sudo tee -a /home/deploy/.ssh/authorized_keys
sudo chmod 600 /home/deploy/.ssh/authorized_keys
sudo chown -R deploy:deploy /home/deploy/.ssh

# Create application directory
sudo mkdir -p /opt/skilloka
sudo chown -R deploy:deploy /opt/skilloka
```

### Required server software

```bash
# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker deploy

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker-compose --version
```

---

## Security Best Practices

1. **Rotate secrets regularly** - Update SSH keys and tokens every 90 days
2. **Use environment-specific secrets** - Don't reuse staging secrets in production
3. **Limit secret access** - Only add collaborators who need deployment permissions
4. **Audit secret usage** - Review Actions logs for secret access patterns
5. **Use OIDC where possible** - For cloud providers that support it

---

## Troubleshooting

### "Permission denied" during deployment
- Verify SSH key is correctly added to server's authorized_keys
- Check file permissions: `~/.ssh` (700), `authorized_keys` (600)
- Ensure deploy user has docker group membership

### Docker login fails
- Verify registry URL is correct
- For GHCR, use a PAT with packages:write scope
- Check token hasn't expired

### Sentry release creation fails
- Verify auth token has correct scopes
- Check org slug matches exactly
- Ensure project exists in Sentry

---

*Last Updated: 2026-02-07*

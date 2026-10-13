# ☁️ FlatConnect Cloud Deployment Strategy

## 🎯 Deployment Architecture Overview

FlatConnect requires:
- **Database:** PostgreSQL (relational data with complex queries)
- **File Storage:** For PDF receipts and logs
- **Queue System:** For background jobs (blocking/unblocking clients)
- **Email Service:** For verification and notifications
- **Caching:** For session and application cache

---

## 🗄️ Database Choice: **PostgreSQL on Supabase** ✅ Recommended

### Why PostgreSQL over Firestore?

| Feature | PostgreSQL (Supabase) | Firestore |
|---------|----------------------|-----------|
| **Schema Complexity** | ✅ Perfect for relational data | ❌ Document model awkward for relations |
| **Transactions** | ✅ ACID compliant | ⚠️ Limited |
| **Query Flexibility** | ✅ SQL with joins, aggregations | ❌ Limited query operators |
| **Laravel Integration** | ✅ Native Eloquent ORM support | ❌ Requires custom adapter |
| **Cost Predictability** | ✅ Fixed tiers | ⚠️ Pay per read/write |
| **Migration Effort** | ✅ Zero code changes | ❌ Complete rewrite |
| **Free Tier** | ✅ 500MB + 2GB transfer | ✅ 1GB storage |

**Decision:** Use **Supabase** (PostgreSQL) because:
1. Your data model is inherently relational (clients → payments → logs)
2. Laravel is built for SQL databases
3. Zero code migration needed
4. Row-Level Security (RLS) for data isolation

---

## 🏗️ Recommended Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    CLOUDFLARE (CDN + DDoS)              │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────┴───────────────────────────────────┐
│                 RAILWAY/RENDER (Free Tier)               │
│  ┌──────────────────────────────────────────────────┐   │
│  │         Laravel App (Web + Queue Worker)         │   │
│  │  - PHP 8.2                                       │   │
│  │  - Livewire Components                           │   │
│  │  - Background Jobs                               │   │
│  └──────────────────────────────────────────────────┘   │
└─────────────────────┬───────────────────────────────────┘
                      │
        ┌─────────────┼─────────────┐
        │             │             │
┌───────┴──────┐ ┌────┴─────┐ ┌────┴──────────┐
│   SUPABASE   │ │  RESEND  │ │ CLOUDINARY/S3 │
│ (PostgreSQL) │ │  (Email) │ │ (File Storage)│
│              │ │          │ │               │
│ Free: 500MB  │ │ Free:    │ │ Free: 25GB    │
│ 2GB transfer │ │ 3k/mo    │ │ bandwidth     │
└──────────────┘ └──────────┘ └───────────────┘
```

---

## 📦 Migration Plan: SQLite → PostgreSQL

### Step 1: Update Database Configuration

**File:** `config/database.php` (Already configured, just update .env)

**Update `.env`:**
```env
DB_CONNECTION=pgsql
DB_HOST=your-project.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-secure-password
DB_SSLMODE=require
```

### Step 2: Export Current Data (If Any)

```bash
# Backup current SQLite data
php artisan db:seed --class=DatabaseSeeder  # For fresh start, or:

# If you have existing data:
php artisan migrate:fresh --seed  # On local SQLite first
php artisan db:table:dump clients > clients_backup.sql
```

### Step 3: Run Migrations on PostgreSQL

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Run migrations
php artisan migrate:fresh

# Seed initial data (staff accounts, etc.)
php artisan db:seed --class=StaffSeeder
```

### Step 4: Verify Migration

```bash
php artisan tinker
>>> \App\Models\Client::count();
>>> \App\Models\Staff::first();
```

---

## 🎁 Free Tier Deployment Stack

### Option 1: Railway (Recommended) 🚂

**Why Railway:**
- $5 free credit monthly
- Zero-config PostgreSQL
- Automatic deployments from Git
- Built-in Redis
- Simple environment variables

**Setup:**
```bash
# 1. Install Railway CLI
npm install -g @railway/cli

# 2. Login and init
railway login
railway init

# 3. Add PostgreSQL
railway add postgresql

# 4. Deploy
railway up
```

**Cost:** $5/month credit (enough for small app)

### Option 2: Render.com 🎨

**Why Render:**
- Truly free tier (750 hours/month)
- PostgreSQL included
- Auto-SSL certificates
- GitHub integration

**Setup:**
1. Connect GitHub repo
2. Select "Web Service"
3. Build Command: `composer install && php artisan migrate --force`
4. Start Command: `php artisan serve --host=0.0.0.0 --port=8080`

**Limitations:** Spins down after 15min inactivity

### Option 3: Fly.io 🪰

**Free Tier:**
- 3 shared-cpu VMs
- 3GB storage
- 160GB bandwidth

**Setup:**
```bash
flyctl launch
flyctl postgres create
flyctl postgres attach
flyctl deploy
```

---

## 🔒 Security Configuration for Production

### 1. Row-Level Security (RLS) on Supabase

**Prevent data leakage between clients:**

```sql
-- Enable RLS on all tables
ALTER TABLE clients ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE admin_logs ENABLE ROW LEVEL SECURITY;

-- Clients can only see their own data
CREATE POLICY "Clients view own data" ON clients
    FOR SELECT
    USING (auth.uid() = id);  -- Adjust based on your auth

-- Staff can see all data
CREATE POLICY "Staff view all clients" ON clients
    FOR ALL
    USING (auth.jwt() ->> 'role' = 'staff');
```

### 2. Environment Variables for Production

**Required `.env` variables:**
```env
APP_NAME=FlatConnect
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_strong_password

# Session Security
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_DOMAIN=.your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Cache
CACHE_STORE=redis
REDIS_HOST=redis.railway.internal
REDIS_PASSWORD=your_redis_password

# Queue
QUEUE_CONNECTION=database  # Or redis for better performance

# Mail (Use Resend or Mailgun)
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_xxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@flatconnect.com

# TP-Link (Keep secure!)
EAP610_BASE_URL=http://192.168.1.11
EAP610_USERNAME=${TPLINK_USER}
EAP610_PASSWORD=${TPLINK_PASS}

# PayMongo
PAYMONGO_SECRET_KEY=sk_live_xxxxxxxxxxxx
PAYMONGO_PUBLIC_KEY=pk_live_xxxxxxxxxxxx

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=warning  # Don't log everything in production
```

### 3. Deployment Checklist

```bash
# 1. Generate new APP_KEY
php artisan key:generate --show

# 2. Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Run migrations
php artisan migrate --force

# 4. Set proper permissions
chmod -R 755 storage bootstrap/cache
```

---

## 🎟️ Free Trial Strategy

### Database Isolation

**Option A: Subdomain per tenant** (Not needed for your use case)

**Option B: Row-Level Security** (Recommended)
```sql
-- Already handled by guards and Eloquent
-- Each client only queries WHERE client_id = auth()->id()
```

### Rate Limiting

**File:** `app/Http/Kernel.php` (Laravel 12 uses `bootstrap/app.php`)

```php
// Add to bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleApi();
    $middleware->throttleRequests(
        'login:5,1',  // 5 attempts per minute
        'register:3,60' // 3 registrations per hour
    );
})
```

### Resource Protection

**Prevent abuse during free trial:**

1. **Limit Clients per Trial Account:**
```php
// In Staff dashboard
if (Client::count() > 100 && !auth()->user()->is_premium) {
    return back()->with('error', 'Free tier limited to 100 clients. Upgrade to add more.');
}
```

2. **Bandwidth Caps:**
```php
// In ClientDiscoveryService
if (!$client->is_premium) {
    $downloadMbps = min($downloadMbps, 10); // Max 10Mbps for free
    $uploadMbps = min($uploadMbps, 5);     // Max 5Mbps for free
}
```

3. **Payment Method Verification:**
```php
// Require card on file even for free trial
// Prevents disposable account abuse
```

### Budget Protection on Cloud Providers

**Railway:**
```bash
railway variables set MAX_MONTHLY_SPEND=25
railway variables set ALERT_THRESHOLD=20
```

**Supabase:**
- Dashboard → Settings → Billing → Set spending cap ($0 for free only)

**Cloudflare:**
- Free tier has no charges for basic DDoS protection

---

## 📊 Cost Estimation

### Free Tier (First 3 Months)
| Service | Free Tier | Limit |
|---------|-----------|-------|
| Railway | $5 credit/mo | ~100 hours uptime |
| Supabase | Free | 500MB DB |
| Resend | Free | 3,000 emails/mo |
| Cloudflare | Free | Unlimited bandwidth |
| **Total** | **$0/month** | **~100 users** |

### Paid Tier (After Growth)
| Service | Cost | Supports |
|---------|------|----------|
| Railway | $20/mo | 24/7 uptime |
| Supabase Pro | $25/mo | 8GB DB, 50GB transfer |
| Resend Growth | $20/mo | 50k emails |
| **Total** | **$65/month** | **~1,000 users** |

---

## 🚀 Deployment Steps

### Pre-Deployment

```bash
# 1. Test locally with PostgreSQL
docker-compose up -d postgres
php artisan migrate:fresh --seed
php artisan serve

# 2. Run tests (if you have them)
php artisan test

# 3. Security audit
composer audit
npm audit
```

### Deploy to Railway

```bash
# 1. Install Railway CLI
npm install -g @railway/cli

# 2. Login
railway login

# 3. Initialize project
railway init

# 4. Add services
railway add  # Select PostgreSQL
railway add  # Select Redis (optional)

# 5. Set environment variables
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
# ... (copy from .env.example)

# 6. Deploy
git push railway main  # Or use: railway up

# 7. Run migrations
railway run php artisan migrate --force

# 8. Create first admin account
railway run php artisan tinker
>>> \App\Models\Staff::create([
...   'name' => 'Admin',
...   'email' => 'admin@flatconnect.com',
...   'password' => bcrypt('secure-password-here')
... ]);
```

### Post-Deployment Verification

```bash
# Check logs
railway logs

# Test endpoints
curl https://your-app.railway.app/login
curl https://your-app.railway.app/register

# Monitor database
railway run php artisan db:show
```

---

## 🔄 CI/CD Pipeline

**File:** `.github/workflows/deploy.yml`

```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install Dependencies
        run: composer install --no-dev --optimize-autoloader
      
      - name: Run Tests
        run: php artisan test
      
      - name: Security Audit
        run: composer audit
      
      - name: Deploy to Railway
        run: |
          npm install -g @railway/cli
          railway up --service web
        env:
          RAILWAY_TOKEN: ${{ secrets.RAILWAY_TOKEN }}
```

---

## 📡 Monitoring Setup

### Application Performance Monitoring

**Install Sentry:**
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your-dsn-here
```

### Uptime Monitoring

**Use:** UptimeRobot (Free for 50 monitors)
- Monitor: https://your-app.com/health
- Alert on: 5xx errors, downtime > 5min

### Create Health Check Endpoint

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => Cache::has('health_check') ? 'ok' : 'miss',
    ]);
});
```

---

## 🆘 Troubleshooting

### Issue: "SQLSTATE[08006] Connection refused"
**Solution:** Check DB credentials and firewall rules

### Issue: "Class 'Redis' not found"
**Solution:** `sudo apt-get install php8.2-redis` or use database cache

### Issue: "419 Page Expired" on forms
**Solution:** Add `@csrf` to all forms, check session driver

### Issue: Background jobs not running
**Solution:** Run queue worker: `php artisan queue:work --daemon`

---

## 📞 Support Contacts

- **Railway Support:** support@railway.app
- **Supabase Support:** support@supabase.io
- **PayMongo Support:** developers@paymongo.com

---

**Document Version:** 1.0  
**Last Updated:** January 2025

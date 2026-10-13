# 🗺️ FlatConnect Implementation Roadmap

## 📋 Project Status Overview

### ✅ Completed (75% Feature Complete)
- [x] Dual authentication system (Staff/Client)
- [x] TP-Link Omada integration
- [x] Client discovery and synchronization
- [x] Block/unblock functionality
- [x] Bandwidth limiting
- [x] Payment processing (PayMongo)
- [x] Admin audit logging
- [x] Email verification flow
- [x] Rate limiting on authentication
- [x] Livewire reactive components

### ⚠️ Security Fixed (Just Now)
- [x] Hardcoded credentials removed
- [x] Command injection patched
- [x] Debug routes removed
- [x] Payment verification implemented
- [x] Password reset infrastructure added
- [x] Environment variable structure improved

### 🚧 Remaining Work (25%)
- [ ] Additional security hardening
- [ ] Production deployment setup
- [ ] Monitoring and alerting
- [ ] Backup strategy
- [ ] Documentation completion
- [ ] Testing suite
- [ ] Performance optimization

---

## 🎯 Priority Task List

## **PHASE 1: Pre-Deployment Security** (1-2 Days)

### Priority: 🔴 CRITICAL

#### Task 1.1: Credential Rotation & Git History Cleanup
**Estimated Time:** 2 hours

```bash
# ⚠️ IMPORTANT: Do this BEFORE deploying!

# 1. Change TP-Link admin password
# Access your TP-Link Omada controller and change:
# Username: AdminExistentialCrisis → (new secure username)
# Password: 66E652DD4948E20796A7B37C64FC0079 → (new strong password)

# 2. Update .env file (never commit this!)
cp .env.example .env
# Edit .env and set all real values

# 3. Purge secrets from Git history
git filter-repo --path app/Services/Tplink/ClientDiscoveryService.php --invert-paths
# Or use BFG Repo-Cleaner:
java -jar bfg.jar --delete-files ClientDiscoveryService.php
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# 4. Force push (WARNING: coordinates with team first!)
git push origin --force --all
```

**Verification:**
```bash
# Check that secrets are gone:
git log -p --all -S "AdminExistentialCrisis"
# Should return nothing
```

---

#### Task 1.2: Add Security Middleware
**Estimated Time:** 30 minutes

**File:** `bootstrap/app.php` (Laravel 12)

```php
<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ValidateMacAddress;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Apply security headers to all routes
        $middleware->append(SecurityHeaders::class);
        
        // Apply to specific route groups
        $middleware->group('staff', [
            'auth:staff',
        ]);
        
        $middleware->group('client', [
            'auth:client',
        ]);
        
        // Throttle API endpoints
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Don't leak sensitive error details in production
        $exceptions->render(function (Throwable $e) {
            if (app()->environment('production')) {
                return response()->view('errors.500', [], 500);
            }
        });
    })->create();
```

**Test:**
```bash
php artisan serve
curl -I http://localhost:8000/login
# Should see X-Frame-Options, CSP, etc.
```

---

#### Task 1.3: Run Security Audit Command
**Estimated Time:** 15 minutes

```bash
# Run the custom security audit
php artisan app:security-audit

# Run Composer security audit
composer audit

# Fix any vulnerabilities:
composer update --with-dependencies
```

---

#### Task 1.4: Configure Production Environment
**Estimated Time:** 1 hour

**Create:** `.env.production` (template for deployment)

```env
# === APPLICATION ===
APP_NAME=FlatConnect
APP_ENV=production
APP_KEY=base64:GENERATE_WITH_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://your-domain.com

# === DATABASE (Supabase PostgreSQL) ===
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=STRONG_PASSWORD_HERE

# === SESSION (Secure in Production) ===
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_DOMAIN=.your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_HTTP_ONLY=true

# === CACHE & QUEUE ===
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis.railway.internal
REDIS_PASSWORD=REDIS_PASS_HERE
REDIS_PORT=6379

# === MAIL (Production) ===
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_XXXXXXXXXXXXXXXX
MAIL_FROM_ADDRESS=noreply@flatconnect.com
MAIL_FROM_NAME="${APP_NAME}"

# === TP-LINK OMADA ===
EAP610_BASE_URL=http://192.168.1.11
EAP610_USERNAME=NEW_SECURE_USERNAME
EAP610_PASSWORD=NEW_SECURE_PASSWORD

# === PAYMONGO (PRODUCTION KEYS) ===
PAYMONGO_SECRET_KEY=sk_live_XXXXXXXXXXXXXXXXX
PAYMONGO_PUBLIC_KEY=pk_live_XXXXXXXXXXXXXXXXX

# === LOGGING ===
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning
LOG_DEPRECATIONS_CHANNEL=null

# === SECURITY ===
BCRYPT_ROUNDS=12
```

**Checklist:**
- [ ] All placeholders replaced with real values
- [ ] Passwords are strong (20+ chars, mixed case, symbols)
- [ ] Production keys from PayMongo dashboard
- [ ] Database credentials from Supabase
- [ ] APP_DEBUG set to false

---

## **PHASE 2: Database Migration** (2-3 Hours)

### Priority: 🟡 HIGH

#### Task 2.1: Set Up Supabase Project
**Estimated Time:** 30 minutes

1. Go to [supabase.com](https://supabase.com) and create account
2. Create new project:
   - Name: `flatconnect-prod`
   - Region: Choose closest to your users
   - Database password: Generate strong password
3. Wait for provisioning (~2 minutes)
4. Copy connection details:
   - Settings → Database → Connection string
   - Settings → API → anon/service_role keys (if using Supabase Auth)

**Save credentials to `.env.production`**

---

#### Task 2.2: Test Database Connection
**Estimated Time:** 15 minutes

```bash
# Update local .env with Supabase credentials temporarily
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
# ... (other Supabase settings)

# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::connection()->getDatabaseName();

# If successful:
✅ "postgres"
```

**Troubleshooting:**
- Error: `SQLSTATE[08006]` → Check firewall, verify IP not blocked
- Error: `Access denied` → Verify username/password
- Error: `SSL required` → Add `DB_SSLMODE=require` to .env

---

#### Task 2.3: Run Migrations on Production Database
**Estimated Time:** 15 minutes

```bash
# Dry run (check SQL without executing)
php artisan migrate --pretend

# Run migrations
php artisan migrate --force

# Verify tables created
php artisan tinker
>>> DB::table('clients')->count();
>>> DB::table('staff')->count();
```

**Expected tables:**
- `cache`, `cache_locks`
- `sessions`
- `jobs`, `job_batches`, `failed_jobs`
- `clients`, `staff`
- `payments`, `admin_logs`
- `discovered_clients`, `client_limits`
- `migrations`

---

#### Task 2.4: Seed Initial Data
**Estimated Time:** 30 minutes

**Create first admin account:**

```bash
php artisan tinker

>>> use App\Models\Staff;
>>> use Illuminate\Support\Facades\Hash;

>>> Staff::create([
...   'name' => 'Admin',
...   'email' => 'admin@flatconnect.com',
...   'password' => Hash::make('CHANGE_THIS_SECURE_PASSWORD')
... ]);

# Verify
>>> Staff::first();
```

**Optional: Seed sample clients for testing**

```bash
php artisan db:seed --class=ClientSeeder
```

---

#### Task 2.5: Enable Row-Level Security (RLS)
**Estimated Time:** 1 hour

**Access Supabase SQL Editor:**
Dashboard → SQL Editor → New Query

```sql
-- Enable RLS on sensitive tables
ALTER TABLE clients ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE admin_logs ENABLE ROW LEVEL SECURITY;
ALTER TABLE sessions ENABLE ROW LEVEL SECURITY;

-- Policy: Clients can only view their own data
CREATE POLICY "clients_select_own_data" 
ON clients 
FOR SELECT 
TO authenticated
USING (id = auth.uid()::uuid);  -- Adjust based on your auth setup

-- Policy: Clients can update their own profile
CREATE POLICY "clients_update_own_data"
ON clients
FOR UPDATE
TO authenticated
USING (id = auth.uid()::uuid);

-- Policy: Staff can view all clients
CREATE POLICY "staff_view_all_clients"
ON clients
FOR ALL
TO authenticated
USING (
  EXISTS (
    SELECT 1 FROM staff 
    WHERE staff.id = auth.uid()::uuid
  )
);

-- Policy: Only authenticated users can view payments
CREATE POLICY "payments_own_data"
ON payments
FOR SELECT
TO authenticated
USING (
  client_id IN (
    SELECT id FROM clients WHERE id = auth.uid()::uuid
  )
  OR
  EXISTS (SELECT 1 FROM staff WHERE id = auth.uid()::uuid)
);

-- Verify policies
SELECT schemaname, tablename, policyname, permissive, roles, cmd, qual
FROM pg_policies
WHERE tablename IN ('clients', 'payments', 'admin_logs');
```

**Note:** If not using Supabase Auth, Laravel handles authorization at application level.

---

## **PHASE 3: Deployment Setup** (3-4 Hours)

### Priority: 🟡 HIGH

#### Task 3.1: Choose Hosting Platform
**Recommended:** Railway (easiest) or Render (free tier)

**Railway Setup:**

```bash
# Install CLI
npm install -g @railway/cli

# Login
railway login

# Initialize project
railway init
# Name: flatconnect-production

# Link to GitHub (optional but recommended)
railway link

# Add PostgreSQL (if not using Supabase)
railway add

# Set environment variables
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set APP_KEY=$(php artisan key:generate --show)
# ... (set all .env.production variables)

# Or upload .env file:
railway variables set < .env.production
```

---

#### Task 3.2: Configure Build & Start Commands
**Estimated Time:** 30 minutes

**Create:** `railway.json` or `render.yaml`

**For Railway:**
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS",
    "buildCommand": "composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache"
  },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}",
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

**For Render (render.yaml):**
```yaml
services:
  - type: web
    name: flatconnect
    env: php
    plan: free
    buildCommand: composer install --no-dev --optimize-autoloader
    startCommand: php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: APP_KEY
        sync: false  # Set in dashboard
      # ... (add all env vars)
```

---

#### Task 3.3: Deploy Application
**Estimated Time:** 1 hour

```bash
# Railway
git push railway main

# Or manual deployment
railway up

# Monitor deployment
railway logs --follow

# Check status
railway status
```

**Expected output:**
```
✅ Build successful
✅ Migrations completed
✅ Application running on port 8080
```

**Test deployment:**
```bash
# Get your deployment URL
railway domain

# Test endpoints
curl https://flatconnect-production.up.railway.app/login
```

---

#### Task 3.4: Configure Custom Domain (Optional)
**Estimated Time:** 30 minutes

**Add custom domain:**
```bash
railway domain add flatconnect.com
```

**DNS Configuration:**
```
Type: CNAME
Name: @ (or www)
Value: [provided by Railway/Render]
TTL: 3600
```

**Enable SSL:**
- Railway: Automatic (Let's Encrypt)
- Render: Automatic

**Wait for DNS propagation:** ~15 minutes to 24 hours

---

#### Task 3.5: Set Up Queue Worker
**Estimated Time:** 30 minutes

Your app uses background jobs for blocking/unblocking clients.

**Railway:**
```bash
# Create new service
railway service create queue-worker

# Set environment variables (copy from web service)
railway variables set QUEUE_CONNECTION=database
# ... (same as web service)

# Set start command
railway variables set START_COMMAND="php artisan queue:work --tries=3 --timeout=90"

# Deploy
railway up
```

**Alternative:** Use scheduled command

```bash
# Add to railway.json
"cron": [
  {
    "command": "php artisan queue:work --stop-when-empty",
    "schedule": "* * * * *"
  }
]
```

---

## **PHASE 4: Post-Deployment** (2-3 Hours)

### Priority: 🟢 MEDIUM

#### Task 4.1: Set Up Monitoring
**Estimated Time:** 1 hour

**Option A: Uptime Monitoring (Free)**
1. Sign up at [uptimerobot.com](https://uptimerobot.com)
2. Add monitor:
   - Type: HTTPS
   - URL: `https://your-domain.com/up`
   - Interval: 5 minutes
   - Alert contacts: Your email

**Option B: Application Monitoring (Better)**
1. Sign up at [sentry.io](https://sentry.io)
2. Install Laravel SDK:
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=YOUR_DSN
```

3. Update `.env`:
```env
SENTRY_LARAVEL_DSN=https://xxx@xxx.ingest.sentry.io/xxx
SENTRY_TRACES_SAMPLE_RATE=0.2
```

4. Test:
```bash
php artisan sentry:test
```

---

#### Task 4.2: Configure Automated Backups
**Estimated Time:** 1 hour

**Supabase Backups** (Automatic on Pro plan)
- Daily snapshots (retention: 7 days on free, 30 days on pro)

**Manual Backup Script:**

**Create:** `backup.sh`
```bash
#!/bin/bash

# Database backup
BACKUP_DIR="./backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/flatconnect_$TIMESTAMP.sql"

mkdir -p $BACKUP_DIR

# Export from Supabase
PGPASSWORD=$DB_PASSWORD pg_dump \
  -h $DB_HOST \
  -U $DB_USERNAME \
  -d $DB_DATABASE \
  -F c \
  -f $BACKUP_FILE

# Upload to S3 (optional)
# aws s3 cp $BACKUP_FILE s3://my-backups/

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete

echo "Backup completed: $BACKUP_FILE"
```

**Schedule with cron:**
```bash
# Daily at 3 AM
0 3 * * * /path/to/backup.sh >> /var/log/backup.log 2>&1
```

---

#### Task 4.3: Implement Health Checks
**Estimated Time:** 30 minutes

Laravel 12 includes `/up` endpoint by default.

**Enhance it:**

**File:** `routes/web.php`
```php
Route::get('/health', function () {
    $checks = [
        'status' => 'healthy',
        'database' => 'unknown',
        'cache' => 'unknown',
        'queue' => 'unknown',
        'tplink' => 'unknown',
    ];

    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Exception $e) {
        $checks['database'] = 'error';
        $checks['status'] = 'degraded';
    }

    try {
        Cache::put('health_check', true, 10);
        $checks['cache'] = Cache::get('health_check') ? 'ok' : 'error';
    } catch (\Exception $e) {
        $checks['cache'] = 'error';
    }

    try {
        $pendingJobs = DB::table('jobs')->count();
        $checks['queue'] = "ok ($pendingJobs pending)";
    } catch (\Exception $e) {
        $checks['queue'] = 'error';
    }

    $httpCode = $checks['status'] === 'healthy' ? 200 : 503;

    return response()->json($checks, $httpCode);
})->name('health');
```

**Test:**
```bash
curl https://your-domain.com/health
```

---

#### Task 4.4: Performance Optimization
**Estimated Time:** 1 hour

```bash
# Enable OPcache (production)
# Add to php.ini or .user.ini:
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Enable Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Enable query caching (for dashboard stats)
```

**Add query caching:**

**File:** `app/Livewire/Staff/Dashboard.php`
```php
use Illuminate\Support\Facades\Cache;

public function render()
{
    $stats = Cache::remember('dashboard_stats', 60, function () {
        return [
            'paidCount' => Client::where('payment_status', 'paid')->count(),
            'unpaidCount' => Client::where('payment_status', 'unpaid')->count(),
            'repeaterCount' => Client::whereNotNull('repeater_name')->count(),
        ];
    });

    return view('livewire.staff.dashboard', $stats)
        ->layout('layouts.app', ['title' => 'Admin Dashboard']);
}
```

---

## **PHASE 5: Documentation & Testing** (2-3 Days)

### Priority: 🟢 MEDIUM

#### Task 5.1: Write API Documentation
**If building API endpoints for mobile app or integrations**

**Tools:** 
- Scribe: `composer require knuckleswtf/scribe`
- Postman collections
- OpenAPI/Swagger

---

#### Task 5.2: Write Tests
**Estimated Time:** 1 day

**Example test:**

**Create:** `tests/Feature/AuthTest.php`
```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login()
    {
        $staff = Staff::factory()->create([
            'email' => 'staff@test.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'staff@test.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/staff/dashboard');
        $this->assertAuthenticatedAs($staff, 'staff');
    }

    public function test_rate_limiting_on_login()
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'wrong@test.com',
                'password' => 'wrong'
            ]);
        }

        // Should be rate limited
        $response = $this->post('/login', [
            'email' => 'wrong@test.com',
            'password' => 'wrong'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }
}
```

**Run tests:**
```bash
php artisan test
```

---

#### Task 5.3: Create User Documentation
**Estimated Time:** 4 hours

**Create:** `docs/USER_GUIDE.md`

Topics:
- How to register as a client
- How staff can manage clients
- How to process payments
- How to block/unblock users
- How to view logs
- Troubleshooting common issues

---

## **PHASE 6: Launch Preparation** (1 Day)

### Priority: 🔴 CRITICAL

#### Task 6.1: Pre-Launch Checklist

**Security:**
- [ ] All secrets rotated
- [ ] Git history cleaned
- [ ] Security audit passed
- [ ] Dependencies updated
- [ ] HTTPS enabled with valid certificate
- [ ] Security headers configured
- [ ] Rate limiting enabled

**Functionality:**
- [ ] Staff can login
- [ ] Clients can register
- [ ] TP-Link integration working
- [ ] Payments processing correctly
- [ ] Email notifications sending
- [ ] Blocking/unblocking works

**Operations:**
- [ ] Database backups scheduled
- [ ] Monitoring configured
- [ ] Logs aggregating properly
- [ ] Queue worker running
- [ ] Health checks passing

**Legal:**
- [ ] Privacy policy published
- [ ] Terms of service published
- [ ] Cookie consent (if EU users)
- [ ] Data processing agreement (GDPR)

---

#### Task 6.2: Soft Launch (Beta Testing)
**Estimated Time:** 1 week

1. **Invite 5-10 beta testers**
2. **Monitor closely:**
   - Check logs every day
   - Track performance metrics
   - Gather user feedback
3. **Fix critical bugs**
4. **Iterate on UX issues**

---

#### Task 6.3: Full Launch
**Estimated Time:** 1 day

1. **Final smoke tests**
2. **Announce on social media / website**
3. **Monitor for first 24 hours**
4. **Have rollback plan ready**

---

## 📅 Timeline Summary

| Phase | Duration | Priority | Can Start |
|-------|----------|----------|-----------|
| Phase 1: Security | 1-2 days | 🔴 Critical | Immediately |
| Phase 2: Database | 3 hours | 🟡 High | After Phase 1 |
| Phase 3: Deployment | 4 hours | 🟡 High | After Phase 2 |
| Phase 4: Post-Deploy | 3 hours | 🟢 Medium | After Phase 3 |
| Phase 5: Docs/Tests | 2-3 days | 🟢 Medium | Parallel to Phase 1-3 |
| Phase 6: Launch | 1-2 weeks | 🔴 Critical | After all above |

**Total Estimated Time:** 2-3 weeks for full production readiness

---

## 🚦 Risk Assessment

### High Risk Items
1. **Credential rotation** - Could lock you out if done wrong
2. **Database migration** - Could lose data
3. **Payment integration** - Must be tested thoroughly

### Mitigation Strategies
1. **Always backup before major changes**
2. **Test on staging environment first**
3. **Have rollback plan ready**
4. **Keep old credentials until verified new ones work**

---

## 📞 Support Resources

- **Laravel Docs:** https://laravel.com/docs/12.x
- **Livewire Docs:** https://livewire.laravel.com/docs
- **Supabase Docs:** https://supabase.com/docs
- **Railway Docs:** https://docs.railway.app
- **PayMongo Docs:** https://developers.paymongo.com

---

**Document Version:** 1.0  
**Last Updated:** January 2025

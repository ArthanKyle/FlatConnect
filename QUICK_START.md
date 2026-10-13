# ⚡ FlatConnect Quick Start Guide

## 🚀 Get Production-Ready in 24 Hours

### ⏱️ Critical Path (Do These First)

```bash
# STEP 1: Rotate Compromised Credentials (30 minutes)
# 1. Change TP-Link admin password via web interface
# 2. Update .env file with new credentials
# 3. NEVER commit .env to Git

# STEP 2: Run Security Audit (5 minutes)
composer audit
php artisan app:security-audit

# STEP 3: Set Up Production Database (1 hour)
# → Sign up at supabase.com
# → Create project
# → Copy connection string to .env
php artisan migrate --force
php artisan tinker
>>> \App\Models\Staff::create(['name'=>'Admin', 'email'=>'admin@flatconnect.com', 'password'=>bcrypt('SECURE_PASS')]);

# STEP 4: Deploy to Railway (30 minutes)
npm install -g @railway/cli
railway login
railway init
railway up

# STEP 5: Verify Deployment (15 minutes)
curl https://your-app.railway.app/health
# Test login, registration, payment flow
```

---

## 📋 Essential Environment Variables

```env
# Minimum required for production:
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxxx  # php artisan key:generate
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=db.xxx.supabase.co
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_password

EAP610_BASE_URL=http://192.168.1.11
EAP610_USERNAME=your_tplink_username
EAP610_PASSWORD=your_tplink_password

PAYMONGO_SECRET_KEY=sk_live_xxxxx
PAYMONGO_PUBLIC_KEY=pk_live_xxxxx

SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
```

---

## 🔒 Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong APP_KEY generated
- [ ] TP-Link credentials rotated
- [ ] Payment keys are LIVE not TEST
- [ ] HTTPS enabled
- [ ] Session encryption enabled
- [ ] Security headers middleware active
- [ ] Rate limiting configured
- [ ] Backups scheduled

---

## 🐛 Common Issues & Fixes

### "419 Page Expired" on forms
```php
// Add to blade files:
@csrf
```

### Payment callback not working
```bash
# Check logs:
php artisan log:tail

# Verify PayMongo webhook endpoint:
# Should be: https://your-domain.com/client/renew/callback
```

### Queue jobs not processing
```bash
# Make sure queue worker is running:
php artisan queue:work --daemon

# Or add to Railway:
railway service create queue-worker
```

### TP-Link connection fails
```bash
# Test from server:
curl -v http://192.168.1.11

# Check firewall rules
# Verify VPN/network access if on cloud
```

---

## 📊 Monitoring Commands

```bash
# Check app health
curl https://your-domain.com/health

# View logs
railway logs --tail 100

# Database status
php artisan db:show

# Queue status
php artisan queue:monitor

# Failed jobs
php artisan queue:failed
```

---

## 🔄 Common Operations

### Deploy update
```bash
git push railway main
# Or: railway up
```

### Run migrations
```bash
railway run php artisan migrate --force
```

### Clear cache
```bash
railway run php artisan cache:clear
railway run php artisan config:clear
```

### Create admin user
```bash
railway run php artisan tinker
>>> \App\Models\Staff::create([...]);
```

---

## 📞 Emergency Contacts

- **Railway Support:** support@railway.app
- **Supabase Support:** support@supabase.io  
- **PayMongo Support:** developers@paymongo.com

---

## 🎯 Next Steps After Deployment

1. **Week 1:** Monitor closely, fix bugs
2. **Week 2:** Gather user feedback
3. **Week 3:** Performance optimization
4. **Month 2:** Add missing features
5. **Month 3:** Scale infrastructure if needed

---

## 💡 Pro Tips

- **Always test on staging first**
- **Keep .env.example up to date**
- **Document all custom configurations**
- **Set up alerts for errors**
- **Review security logs weekly**
- **Backup before major changes**

---

**Need Help?** Check:
1. `SECURITY_AUDIT.md` - Security details
2. `DEPLOYMENT_STRATEGY.md` - Cloud setup
3. `IMPLEMENTATION_ROADMAP.md` - Full task list

---

**Version:** 1.0  
**Updated:** January 2025

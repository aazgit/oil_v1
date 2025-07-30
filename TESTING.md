# 🧪 Testing Guide - KishansKraft E-commerce Platform

## Quick Testing Access Methods

### 🚀 Method 1: Test Login Page (Fastest)
**Perfect for: Quick testing without any OTP hassle**

1. **Access the test login page:**
   ```
   http://localhost:8080/test-login.php
   ```

2. **Use any mobile number:**
   - Test account: `9876543210`
   - Admin account: `9123456789` (if created)
   - Or any 10-digit number for instant account creation

3. **Click "Quick Login (No OTP)"** - you'll be logged in immediately!

---

### 🔐 Method 2: Regular Login with Test OTP 
**Perfect for: Testing the complete OTP flow**

1. **Go to the main site:**
   ```
   http://localhost:8080/frontend/index.html
   ```

2. **Click "Login" and enter any mobile number**

3. **When prompted for OTP, use the universal test code:**
   ```
   123456
   ```

4. **This works for ANY mobile number in debug mode!**

---

### 🛠️ Method 3: API Direct Testing
**Perfect for: Developers testing the API directly**

#### Test Login API (No OTP)
```bash
curl -X POST http://localhost:8080/backend/api/auth/test-login \
  -H "Content-Type: application/json" \
  -d '{"mobile": "9876543210"}' \
  -c cookies.txt
```

#### Regular Login with Test OTP
```bash
# Step 1: Send OTP (will return OTP in debug mode)
curl -X POST http://localhost:8080/backend/api/auth/send-otp \
  -H "Content-Type: application/json" \
  -d '{"mobile": "9876543210", "purpose": "login"}'

# Step 2: Verify with universal test OTP
curl -X POST http://localhost:8080/backend/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"mobile": "9876543210", "otp": "123456", "purpose": "login"}' \
  -c cookies.txt
```

---

## 📋 Test Accounts Available

### Pre-created Test User
- **Mobile:** `9876543210`
- **Email:** `test@kishanskraft.com`
- **Name:** Test User
- **Status:** Verified and ready to use

### Admin Account (if created during installation)
- **Mobile:** `9123456789`
- **Email:** `admin@kishanskraft.com`
- **Name:** Admin User
- **Status:** Full admin privileges

### Dynamic Test Accounts
- **Any 10-digit mobile number** will work
- The system automatically creates test accounts when using test-login
- All auto-created accounts are immediately verified

---

## 🎯 Testing Scenarios

### 1. **Quick User Testing**
```
1. Go to: http://localhost:8080/test-login.php
2. Enter: 9876543210
3. Click: Quick Login (No OTP)
4. Result: Instant login to main site
```

### 2. **OTP Flow Testing**
```
1. Go to: http://localhost:8080/frontend/index.html
2. Click: Login button
3. Enter: Any 10-digit mobile number
4. Click: Send OTP
5. Enter OTP: 123456
6. Result: Successfully logged in
```

### 3. **New User Registration Testing**
```
1. Go to: http://localhost:8080/frontend/index.html
2. Click: Register/Sign up
3. Enter: Any new mobile number
4. Fill: Name, email, address details
5. Enter OTP: 123456
6. Result: Account created and logged in
```

### 4. **Shopping Cart Testing**
```
1. Login using any method above
2. Browse products and add to cart
3. Go to cart and checkout
4. Test the complete purchase flow
```

---

## 🔧 Developer Features

### Debug Mode Features (APP_DEBUG = true)
- ✅ **Universal OTP:** `123456` works for any mobile number
- ✅ **Test Login API:** Bypasses all authentication
- ✅ **Auto Account Creation:** Non-existent users are created automatically
- ✅ **OTP in Response:** Real OTP codes shown in API responses
- ✅ **Detailed Error Messages:** Full error context for debugging

### Production Safety
- 🔒 Test login API is **disabled** when `APP_DEBUG = false`
- 🔒 Universal OTP only works in debug mode
- 🔒 Real SMS integration replaces debug responses
- 🔒 Account creation requires proper verification

---

## 🚨 Security Notes

### Development Environment
- **Universal OTP (123456)** only works when `APP_DEBUG = true`
- **Test login endpoint** is automatically disabled in production
- **Real OTP codes** are still generated and can be used normally

### Production Deployment
- Set `APP_DEBUG = false` in `/backend/config/config.php`
- Configure real SMS provider credentials
- Remove or restrict access to `test-login.php`
- Use proper SSL/HTTPS encryption

---

## 🐛 Troubleshooting

### Login Issues
```
Problem: "Login failed - please check if the server is running"
Solution: Make sure PHP server is running: php -S localhost:8080
```

```
Problem: "Mobile number not registered"
Solution: Use test-login.php or register the number first
```

```
Problem: "Invalid or expired OTP"
Solution: Use universal test OTP: 123456 (in debug mode)
```

### Session Issues
```
Problem: Getting logged out frequently
Solution: Check session configuration in php.ini
```

```
Problem: Login works but can't access protected pages
Solution: Ensure cookies are enabled and session is maintained
```

---

## 🎪 Fun Testing Tips

### Rapid Testing Workflow
1. **Bookmark:** `http://localhost:8080/test-login.php`
2. **Use:** Mobile number `9876543210` (pre-filled)
3. **Click:** Quick Login button
4. **Result:** Instant access to test the entire platform!

### Multi-User Testing
- Use different mobile numbers: `9876543210`, `9876543211`, `9876543212`
- Each creates a separate test account
- Test different user scenarios simultaneously

### API Testing with Browser
- Use browser developer tools (F12)
- Network tab shows all API calls
- Console tab for JavaScript debugging
- Application tab for session/cookie inspection

---

## 📞 Need Help?

- **Test Login Page:** `http://localhost:8080/test-login.php`
- **API Console:** `http://localhost:8080/dev-console.html`
- **Main Application:** `http://localhost:8080/frontend/index.html`
- **Installation Guide:** `/INSTALLATION.md`

**Happy Testing! 🎉**

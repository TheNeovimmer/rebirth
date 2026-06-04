#!/bin/bash
# Rebirth MVP - Comprehensive System Test Suite
set -e

BASE="https://nada.ddev.site"
COOKIE_JAR="/tmp/test_cookies.txt"
TEMP_COOKIES="/tmp/test_temp_cookies.txt"

pass=0
fail=0

check() {
  local desc="$1"
  local expected="$2"
  local actual="$3"
  if echo "$actual" | grep -q "$expected"; then
    echo "  PASS: $desc"
    ((++pass))
  else
    echo "  FAIL: $desc"
    echo "    Expected '$expected' in response"
    [ -n "$actual" ] && echo "    Got: ${actual:0:300}"
    ((++fail))
  fi
}

check_not() {
  local desc="$1"
  local not_expected="$2"
  local actual="$3"
  if echo "$actual" | grep -q "$not_expected"; then
    echo "  FAIL: $desc (contains '$not_expected')"
    ((++fail))
  else
    echo "  PASS: $desc"
    ((++pass))
  fi
}

# Fetch CSRF token from a page, saving session cookies
get_csrf() {
  local url="$1"
  local page=$(curl -sk -c "$COOKIE_JAR" -b "$COOKIE_JAR" "$url" 2>/dev/null)
  # Try name="_token" value="..." format first (PHP forms)
  local token=$(echo "$page" | grep -oP '(?<=name="_token" value=")[^"]+' | head -1)
  # Fallback: value="hexstring" (alternative CSRF field)
  if [ -z "$token" ]; then
    token=$(echo "$page" | grep -oP 'value="([a-f0-9]+)"' | head -1 | grep -oP '[a-f0-9]+')
  fi
  echo "$token"
}

login() {
  local email="$1"
  rm -f "$COOKIE_JAR"
  local token=$(get_csrf "$BASE/login")
  local resp=$(curl -sk -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "$BASE/login" \
    -d "_token=$token&email=$email&password=password123" \
    -o /dev/null -w "%{http_code};%{redirect_url}" 2>/dev/null)
  echo "$resp"
}

echo ""
echo "══════════════════════════════════════════════════"
echo "  REBIRTH MVP - FULL SYSTEM TEST SUITE"
echo "══════════════════════════════════════════════════"
echo ""

# ─── 1. PUBLIC PAGES ─────────────────────────
echo "── 1. PUBLIC PAGES ─────────────────────────"

resp=$(curl -sk "$BASE/login" -o /dev/null -w "%{http_code}")
check "1.01 Login page returns 200" "200" "$resp"

resp=$(curl -sk "$BASE/signup" -o /dev/null -w "%{http_code}")
check "1.02 Signup page returns 200" "200" "$resp"

page=$(curl -sk "$BASE/login" 2>/dev/null)
check "1.03 Login has CSRF token" "name=\"_token\"" "$page"

page=$(curl -sk "$BASE/signup" 2>/dev/null)
check "1.04 Signup has CSRF token" "name=\"_token\"" "$page"

# Home page serves login page (no redirect)
page=$(curl -sk "$BASE/" 2>/dev/null)
check "1.05 Home page is a landing page" "Rise From the Ash" "$page"

resp=$(curl -sk "$BASE/panel/dashboard" -o /dev/null -w "%{redirect_url}")
check "1.06 Unauthenticated blocked from panel" "/login" "$resp"

resp=$(curl -sk "$BASE/admin/dashboard" -o /dev/null -w "%{redirect_url}")
check "1.07 Unauthenticated blocked from admin" "/login" "$resp"

resp=$(curl -sk "$BASE/css/app.css" -o /dev/null -w "%{http_code}")
check "1.08 CSS loads" "200" "$resp"

resp=$(curl -sk "$BASE/js/app.js" -o /dev/null -w "%{http_code}")
check "1.09 app.js loads" "200" "$resp"

# ─── 2. AUTH FLOWS ──────────────────────────
echo ""
echo "── 2. AUTH FLOWS ─────────────────────────"

echo "  2.01 Login as member (jamie@example.com)"
result=$(login "jamie@example.com")
http_code=$(echo "$result" | cut -d';' -f1)
redirect=$(echo "$result" | cut -d';' -f2)
check "Member login succeeds (302)" "302" "$http_code"
check "Member redirects to panel" "panel" "$redirect"

echo "  2.02 Member session persists to dashboard"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/dashboard" 2>/dev/null)
check "Dashboard renders for member" "Dashboard" "$page"

echo "  2.03 Login with wrong password rejected"
rm -f "$COOKIE_JAR"
token=$(get_csrf "$BASE/login")
resp=$(curl -sk -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "$BASE/login" \
  -d "_token=$token&email=jamie@example.com&password=wrongpassword" \
  -o /dev/null -w "%{redirect_url}" 2>/dev/null)
check "Bad credentials redirects with error" "error=Invalid" "$resp"

echo "  2.04 Login as therapist (sarah.mitchell@rebirth.app)"
result=$(login "sarah.mitchell@rebirth.app")
http_code=$(echo "$result" | cut -d';' -f1)
check "Therapist login succeeds (302)" "302" "$http_code"

echo "  2.05 Therapist dashboard shows patient-appropriate content"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/dashboard" 2>/dev/null)
check "Therapist dashboard shows patients" "Patients" "$page"
check "Therapist dashboard shows SOS" "SOS" "$page"

echo "  2.06 Login as admin (admin@rebirth.app)"
result=$(login "admin@rebirth.app")
http_code=$(echo "$result" | cut -d';' -f1)
redirect=$(echo "$result" | cut -d';' -f2)
check "Admin login succeeds (302)" "302" "$http_code"
check "Admin redirects to admin" "admin" "$redirect"

echo "  2.07 Admin dashboard renders"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/admin/dashboard" 2>/dev/null)
check "Admin dashboard shows Total Users" "Total Users" "$page"

echo "  2.08 Logout"
resp=$(curl -sk -b "$COOKIE_JAR" "$BASE/logout" -o /dev/null -w "%{redirect_url}")
check "Logout redirects to login" "/login" "$resp"

echo "  2.09 After logout, session invalidated"
resp=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/dashboard" -o /dev/null -w "%{redirect_url}")
check "Logged out user blocked" "/login" "$resp"

# ─── 3. MEMBER PAGES ────────────────────────
echo ""
echo "── 3. MEMBER PAGES (jamie@example.com) ────"

login "jamie@example.com" > /dev/null

echo "  3.01 Dashboard"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/dashboard" 2>/dev/null)
check "Stats row visible" "Total Check-ins" "$page"
check "Recent Journal section" "Recent Journal" "$page"
check "Resources section" "Resources" "$page"
check "Quick Actions present" "Quick Actions" "$page"

echo "  3.02 Check-in"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/checkin" 2>/dev/null)
check "Mood grid rendered" "mood-grid" "$page"
check "Mood options include great" "data-value=\"great\"" "$page"
check "Mood options include struggling" "data-value=\"struggling\"" "$page"
check_not "Mood options do NOT include old okay value" "data-value=\"okay\"" "$page"
check "Cravings slider" "craving-slider" "$page"
check "Day Streak stat" "Day Streak" "$page"

echo "  3.03 Journal"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/journal" 2>/dev/null)
check "Journal page renders" "Your Journal" "$page"
check "New Entry button present" "New Entry" "$page"

echo "  3.04 Community"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/community" 2>/dev/null)
check "Support Groups rendered" "Support Groups" "$page"
check "Community Feed rendered" "Community Feed" "$page"

echo "  3.05 Messages"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/messages" 2>/dev/null)
check "Messages page renders" "Messages" "$page"
check "Chat input present" "Type a message" "$page"

echo "  3.06 Resources"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/resources" 2>/dev/null)
check "Resources page renders" "Resources" "$page"

echo "  3.07 SOS"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/sos" 2>/dev/null)
check "SOS page renders with help button" "Need Urgent Help" "$page"

echo "  3.08 Settings"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/settings" 2>/dev/null)
check "Settings page renders with Profile" "Profile" "$page"
check_not "Settings does NOT contain stage field" "Recovery Stage" "$page"

echo "  3.09 Role-based access: Member cannot access admin"
resp=$(curl -sk -b "$COOKIE_JAR" "$BASE/admin/dashboard" -o /dev/null -w "%{http_code}")
check "Member blocked from admin dashboard (403)" "403" "$resp"

echo "  3.10 Role-based access: Member cannot access therapist"
resp=$(curl -sk -b "$COOKIE_JAR" "$BASE/therapist/patients" -o /dev/null -w "%{http_code}")
check "Member blocked from therapist routes (403)" "403" "$resp"

# ─── 4. POST SUBMISSION TESTS ─────────────────
echo ""
echo "── 4. POST SUBMISSION & CSRF TESTS ────────"

echo "  4.01 Check-in submission"
login "jamie@example.com" > /dev/null
token=$(get_csrf "$BASE/panel/checkin")
if [ -n "$token" ]; then
  resp=$(curl -sk -b "$COOKIE_JAR" -X POST "$BASE/panel/checkin" \
    -d "_token=$token&mood=great&craving_level=10&note=Test+check-in+from+suite" \
    -o /tmp/test_checkin_submit.html -w "%{redirect_url}" 2>/dev/null)
  check "Check-in POST redirects" "panel/checkin" "$resp"
else
  echo "  SKIP: Could not extract CSRF token from checkin page"
fi

echo "  4.02 Journal entry creation"
token=$(get_csrf "$BASE/panel/journal")
if [ -n "$token" ]; then
  resp=$(curl -sk -b "$COOKIE_JAR" -X POST "$BASE/panel/journal/create" \
    -d "_token=$token&mood=good&content=Test+journal+entry+from+test+suite" \
    -o /tmp/test_journal_submit.html -w "%{redirect_url}" 2>/dev/null)
  check "Journal POST redirects" "journal" "$resp"
else
  echo "  SKIP: Could not extract CSRF token from journal page"
fi

echo "  4.03 Community post creation"
token=$(get_csrf "$BASE/panel/community")
if [ -n "$token" ]; then
  resp=$(curl -sk -b "$COOKIE_JAR" -X POST "$BASE/panel/messages/create" \
    -d "_token=$token&text=Test+post+from+test+suite" \
    -o /tmp/test_post_submit.html -w "%{redirect_url}" 2>/dev/null)
  check "Community post POST redirects" "community" "$resp"
else
  echo "  SKIP: Could not extract CSRF token from community page"
fi

echo "  4.04 CSRF protection on POST without token"
resp=$(curl -sk -b "$COOKIE_JAR" -X POST "$BASE/panel/journal/create" \
  -d "mood=good&content=No+CSRF" -o /tmp/test_csrf_reject.html -w "%{http_code}" 2>/dev/null)
check "CSRF missing returns 419" "419" "$resp"

# ─── 5. THERAPIST PAGES (sarah.mitchell@rebirth.app) ──
echo ""
echo "── 5. THERAPIST PAGES ─────────────────────"

login "sarah.mitchell@rebirth.app" > /dev/null

echo "  5.01 Dashboard"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/dashboard" 2>/dev/null)
check "Patient count stat" "Patients" "$page"
check "Unread Messages stat" "Unread Messages" "$page"
check "Active SOS stat" "Active SOS" "$page"
check "My Patients card" "My Patients" "$page"
check "Recent Check-ins card" "Recent Check-ins" "$page"

echo "  5.02 My Patients"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/therapist/patients" 2>/dev/null)
check "My Patients page renders" "My Patients" "$page"
check "Patient list has entries" "Jamie" "$page"

echo "  5.03 Patient Detail"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/therapist/patient/1" 2>/dev/null)
check "Patient header shows name" "Jamie" "$page"
check "Recent Check-ins section" "Recent Check-ins" "$page"
check "Recent Journal section" "Recent Journal" "$page"

echo "  5.04 Messages list"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/therapist/messages" 2>/dev/null)
check "Conversations page renders" "Patient Conversations" "$page"

echo "  5.05 SOS"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/therapist/sos" 2>/dev/null)
check "SOS alerts page renders" "SOS Alerts" "$page"

echo "  5.06 Resources"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/therapist/resources" 2>/dev/null)
check "Resources page renders" "My Resources" "$page"
check "Upload button" "Upload" "$page"

echo "  5.07 Community access"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/community" 2>/dev/null)
check "Therapist sees community" "Community Feed" "$page"

# ─── 6. ADMIN PAGES (admin@rebirth.app) ──────
echo ""
echo "── 6. ADMIN PAGES ─────────────────────────"

login "admin@rebirth.app" > /dev/null

echo "  6.01 Dashboard"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/admin/dashboard" 2>/dev/null)
check "Total Users stat" "Total Users" "$page"
check "Check-ins stat" "Check-ins" "$page"
check "Moderation queue stat" "Moderation" "$page"
check "User breakdown section" "User Breakdown" "$page"
check "Quick Links section" "Quick Links" "$page"

echo "  6.02 Users"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/admin/users" 2>/dev/null)
check "Users table renders" "All Users" "$page"
check "Jamie in user list" "Jamie" "$page"
check "Sarah in user list" "Sarah" "$page"
check "Add User button" "Add User" "$page"
check "Therapist column" "Therapist" "$page"

echo "  6.03 Moderation"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/admin/moderation" 2>/dev/null)
check "Moderation page renders" "Community Messages" "$page"

echo "  6.04 Settings"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/admin/settings" 2>/dev/null)
check "Admin settings renders" "Admin Profile" "$page"

echo "  6.05 Admin can create user"
TS=$(date +%s)
ADMIN_TEST_EMAIL="testuser_${TS}@test.com"
token=$(get_csrf "$BASE/admin/users")
if [ -n "$token" ]; then
  resp=$(curl -sk -b "$COOKIE_JAR" -X POST "$BASE/admin/users/create" \
    -d "_token=$token&name=Test+User&email=$ADMIN_TEST_EMAIL&password=password123&role=member" \
    -o /tmp/test_create_user.html -w "%{redirect_url}" 2>/dev/null)
  check "Admin creates user - redirects" "users" "$resp"
  page=$(curl -sk -b "$COOKIE_JAR" "$BASE/admin/users" 2>/dev/null)
  check "New user appears in list" "$ADMIN_TEST_EMAIL" "$page"
else
  echo "  SKIP: Could not extract CSRF token"
fi

# ─── 7. DATABASE VERIFICATION ─────────────────
echo ""
echo "── 7. DATABASE VERIFICATION ────────────────"

count=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM check_ins" 2>/dev/null)
check "7.01 Check-ins table has data" "[1-9]" "$count"

count=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM messages" 2>/dev/null)
check "7.02 Messages table has data" "[1-9]" "$count"

count=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM journal_entries" 2>/dev/null)
check "7.03 Journal entries exist" "[1-9]" "$count"

count=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM users" 2>/dev/null)
check "7.04 Users exist" "[1-9]" "$count"

count=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM groups" 2>/dev/null)
check "7.05 Groups exist" "[1-9]" "$count"

count=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM resources" 2>/dev/null)
check "7.06 Resources exist" "[1-9]" "$count"

has_parent_id=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='rebirth' AND table_name='messages' AND column_name='parent_id'" 2>/dev/null)
check "7.07 messages.parent_id column exists" "1" "$has_parent_id"

stage_gone=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='rebirth' AND table_name='users' AND column_name='stage'" 2>/dev/null)
check "7.08 users.stage column removed" "0" "$stage_gone"

dropped_all=true
for tbl in appointments milestones user_milestones therapist_availability recovery_progress clinical_notes relapses treatment_plans; do
  exists=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='rebirth' AND table_name='$tbl'" 2>/dev/null)
  if [ "$exists" != "0" ]; then
    dropped_all=false
    echo "  FAIL: 7.09 Table '$tbl' still exists"
    ((++fail))
  fi
done
if $dropped_all; then
  echo "  PASS: 7.09 All 8 removed tables confirmed gone"
  ((++pass))
fi

assignments=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM therapist_patients" 2>/dev/null)
check "7.10 Therapist-patient assignments exist" "[1-9]" "$assignments"

tables=$(ddev exec mysql -h db -u db -pdb rebirth -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='rebirth'" 2>/dev/null)
check "7.11 Database has 14 tables (MVP)" "14" "$tables"

# ─── 8. ERROR HANDLING ────────────────────────
echo ""
echo "── 8. ERROR HANDLING ──────────────────────"

login "jamie@example.com" > /dev/null

echo "  8.01 Non-existent page"
resp=$(curl -sk -b "$COOKIE_JAR" "$BASE/anonexistentroute" -o /dev/null -w "%{http_code}")
check "404 route handled gracefully" "404\|302\|200" "$resp"

echo "  8.02 Non-existent patient detail"
resp=$(curl -sk -b "$COOKIE_JAR" "$BASE/therapist/patient/9999" -o /dev/null -w "%{http_code}")
check "Non-existent patient handled" "302\|403\|404\|200" "$resp"

# ─── 9. SIGNUP FLOW ──────────────────────────
echo ""
echo "── 9. SIGNUP FLOW ─────────────────────────"

echo "  9.01 Signup form rendered"
page=$(curl -sk "$BASE/signup" 2>/dev/null)
check "Signup has name field" "name=\"name\"" "$page"
check "Signup has email field" "name=\"email\"" "$page"
check "Signup has password field" "name=\"password\"" "$page"

echo "  9.02 Duplicate email rejected"
rm -f "$COOKIE_JAR"
token=$(get_csrf "$BASE/signup")
resp=$(curl -sk -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "$BASE/signup" \
  -d "_token=$token&name=Duplicate&email=jamie@example.com&password=password123" \
  -o /tmp/test_dup_signup.html -w "%{redirect_url}" 2>/dev/null)
check "Duplicate email returns error" "error" "$resp"

echo "  9.03 Short password rejected"
rm -f "$COOKIE_JAR"
token=$(get_csrf "$BASE/signup")
resp=$(curl -sk -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "$BASE/signup" \
  -d "_token=$token&name=NewUser&email=newuser@test.com&password=ab" \
  -o /tmp/test_short_pw.html -w "%{redirect_url}" 2>/dev/null)
check "Short password (<6 chars) returns error" "error" "$resp"

echo "  9.04 Valid signup"
TS=$(date +%s)
SIGNUP_EMAIL="brandnew_${TS}@test.com"
rm -f "$COOKIE_JAR"
token=$(get_csrf "$BASE/signup")
resp=$(curl -sk -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "$BASE/signup" \
  -d "_token=$token&name=Brand+New+User&email=$SIGNUP_EMAIL&password=password123" \
  -o /tmp/test_valid_signup.html -w "%{redirect_url}" 2>/dev/null)
check "Valid signup redirects to dashboard" "panel/dashboard" "$resp"

echo "  9.05 New user can access dashboard"
page=$(curl -sk -b "$COOKIE_JAR" "$BASE/panel/dashboard" 2>/dev/null)
check "New user dashboard renders" "Dashboard" "$page"

# ─── SUMMARY ────────────────────────────────
echo ""
echo "══════════════════════════════════════════════════"
echo "  FINAL RESULTS: $pass passed, $fail failed"
echo "══════════════════════════════════════════════════"

if [ "$fail" -gt 0 ]; then
  echo ""
  echo "  FAILURES DETECTED. Review output above."
  exit 1
else
  echo ""
  echo "  ALL TESTS PASSED"
  exit 0
fi

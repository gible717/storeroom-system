# UAT Quick Start Guide
**Sistem Pengurusan Bilik Stor dan Inventori - MPK**

**Purpose:** Step-by-step guide to launch UAT immediately
**Estimated Setup Time:** 30 minutes
**Estimated Testing Duration:** 3-5 days

---

## 🚀 Quick Start: 5 Steps to Begin UAT

### ✅ Step 1: Prepare Test Environment (10 minutes)

1. **Backup your current database:**
   ```bash
   # Windows (Laragon)
   cd C:\laragon\bin\mysql\mysql-8.x\bin
   mysqldump -u root storeroom > C:\laragon\www\storeroom\backup_before_uat.sql
   ```

2. **Load UAT test data:**
   ```bash
   # From the same directory
   mysql -u root storeroom < "C:\laragon\www\storeroom\md files\UAT_TEST_DATA.sql"
   ```

3. **Verify test data loaded:**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Check `staf` table for TEST001, TEST002, ADMIN001 accounts
   - Check `barang` table for UAT products
   - Check `permohonan` table for test requests

**✅ Step 1 Complete!** Test accounts and data are ready.

---

### ✅ Step 2: Print/Prepare Test Documents (5 minutes)

1. **Print or open digitally:**
   - [UAT_TEST_PLAN.md](UAT_TEST_PLAN.md) - Primary testing document
   - [UAT_ISSUE_LOG.md](UAT_ISSUE_LOG.md) - For logging issues
   - [UAT_SIGN_OFF_FORM.md](UAT_SIGN_OFF_FORM.md) - Final approval

2. **Create UAT folder:**
   - Create folder: `C:\UAT_Storeroom_Jan2026\`
   - Copy all UAT documents there
   - Prepare screenshot folder: `C:\UAT_Storeroom_Jan2026\Screenshots\`

**✅ Step 2 Complete!** Documents ready for testing team.

---

### ✅ Step 3: Brief UAT Participants (10 minutes)

**Gather your team:**
- 2-3 Staff testers
- 2 Admin testers
- 1 UAT Coordinator (you)

**Brief them on:**

1. **What is UAT?**
   - Final testing before system goes live
   - Test as real users, not as testers
   - Report EVERYTHING that seems wrong or confusing

2. **Test Accounts:**
   - **Staff:** TEST001, TEST002, TEST003 | Password: `Test@123`
   - **Admin:** ADMIN001, ADMIN002 | Password: `Admin@123`

3. **What to test:**
   - Follow UAT_TEST_PLAN.md scenarios
   - Each tester assigned specific test cases
   - Mark Pass/Fail on each test

4. **How to report issues:**
   - Use UAT_ISSUE_LOG.md template
   - Take screenshots
   - Describe steps clearly
   - Note severity (Critical/High/Medium/Low)

**✅ Step 3 Complete!** Team briefed and ready.

---

### ✅ Step 4: Assign Test Cases (5 minutes)

**Recommended Assignment:**

#### Day 1: Staff Workflow Testing
- **Tester 1 (TEST001):** TC-S-001 to TC-S-005
  - Login, submit request, view history, edit, delete

- **Tester 2 (TEST002):** TC-S-006 to TC-S-010
  - Dashboard, profile, password change, logout

- **Tester 3 (TEST003):** Repeat critical scenarios
  - Verify consistency

#### Day 2: Admin Workflow Testing
- **Admin Tester 1 (ADMIN001):** TC-A-001 to TC-A-009
  - Login, dashboard, review, approve, reject, self-approval test

- **Admin Tester 2 (ADMIN002):** TC-A-010 to TC-A-018
  - Product management, stock adjustment, user management

#### Day 3: Security & Integration
- **All testers:** TC-SEC-001 to TC-SEC-008, TC-INT-001 to TC-INT-003
  - Security tests, Telegram notifications

#### Day 4: Reporting & Edge Cases
- **All testers:** TC-RPT-001 to TC-RPT-005
  - Reports, KEW.PS-8 print
  - Exploratory testing (try to break things!)

#### Day 5: Regression & Final Verification
- **All testers:** Re-test failed scenarios
  - Verify all fixes
  - Final sign-off if all passed

**✅ Step 4 Complete!** Test schedule set.

---

### ✅ Step 5: Start Testing! (Begin UAT)

**Daily Routine:**

**Morning Session (9:00 AM - 12:00 PM):**
1. UAT Coordinator briefs daily goals (10 min)
2. Testers execute assigned test cases (2h 30min)
3. Testers log any issues found (20 min)

**Afternoon Session (2:00 PM - 5:00 PM):**
1. Continue testing (2h 30min)
2. Daily wrap-up meeting (30 min)
   - Review issues logged
   - Prioritize fixes
   - Plan next day

**End of Day:**
- Update UAT_ISSUE_LOG.md
- Update progress in UAT_TEST_PLAN.md
- Report critical issues immediately

**✅ Step 5 Complete!** UAT is now in progress!

---

## 📋 Daily Checklist for UAT Coordinator

### Daily Tasks:
- [ ] Start of day briefing (10 min)
- [ ] Monitor testing progress
- [ ] Help testers with questions
- [ ] Log issues in UAT_ISSUE_LOG.md
- [ ] Coordinate with developers for fixes
- [ ] End of day meeting (30 min)
- [ ] Update UAT dashboard/summary

### Weekly Tasks:
- [ ] Review overall progress vs plan
- [ ] Escalate critical/high issues to management
- [ ] Adjust schedule if needed
- [ ] Prepare weekly report

---

## 🎯 Success Criteria Reminder

**UAT is SUCCESSFUL when:**
- ✅ 100% of Critical tests PASS
- ✅ ≥95% of High priority tests PASS
- ✅ ≥90% of Medium priority tests PASS
- ✅ NO Critical/High issues remain open
- ✅ All security tests PASS
- ✅ UAT team signs off

**If these are met → System APPROVED for production!**

---

## 🆘 Quick Troubleshooting

### "Test account won't login"
- Verify password: `Test@123` or `Admin@123`
- Check if UAT_TEST_DATA.sql ran successfully
- Run this query in phpMyAdmin:
  ```sql
  SELECT ID_staf, nama, is_admin FROM staf WHERE ID_staf LIKE 'TEST%' OR ID_staf LIKE 'ADMIN%';
  ```

### "Test data not showing"
- Re-run UAT_TEST_DATA.sql
- Check if storeroom database selected
- Verify products exist:
  ```sql
  SELECT no_kod, perihal_stok, baki_semasa FROM barang WHERE no_kod LIKE 'UAT%';
  ```

### "Critical issue found - what do I do?"
1. STOP testing that module
2. Log issue immediately as CRITICAL
3. Notify IT Manager + Developer
4. Take screenshots/evidence
5. Move to other test modules
6. Wait for fix before retesting

### "Tester confused about what to test"
- Point them to specific test case in UAT_TEST_PLAN.md
- Show them the "Test Steps" section
- Walk through first test case together
- They should follow steps exactly

---

## 📞 Emergency Contacts

| Issue | Contact | Action |
|-------|---------|--------|
| **Critical Bug** | IT Manager + Developer | Immediate fix required |
| **Database Corrupt** | System Administrator | Restore from backup |
| **Network/Access Issue** | IT Support | Check connectivity |
| **Tester Unavailable** | UAT Coordinator | Reassign test cases |

---

## 📊 Sample Daily Report Template

**UAT Daily Report - Day X**
**Date:** _________________

**Tests Executed:** 15
- Passed: 12
- Failed: 2
- Blocked: 1

**Issues Logged:**
- Critical: 0
- High: 1 (#003 - Approval doesn't update stock)
- Medium: 1 (#004 - Sort button not working)
- Low: 0

**Progress:** 31% complete (15/48 tests)

**Blockers:**
- Cannot test report module until data issue fixed

**Plan for Tomorrow:**
- Retest approval after fix
- Continue with reporting module
- Security testing

---

## ✅ Post-UAT Cleanup (After Sign-Off)

**After UAT is approved:**

1. **Remove test data:**
   ```sql
   DELETE FROM permohonan WHERE ID_pemohon LIKE 'TEST%' OR ID_pemohon LIKE 'ADMIN%';
   DELETE FROM staf WHERE ID_staf LIKE 'TEST%' OR ID_staf LIKE 'ADMIN%';
   DELETE FROM barang WHERE no_kod LIKE 'UAT%';
   DELETE FROM jabatan WHERE nama_jabatan LIKE '%UAT%';
   DELETE FROM KATEGORI WHERE nama_kategori LIKE '%UAT%';
   ```

2. **Or restore clean backup** (recommended):
   ```bash
   mysql -u root storeroom < backup_before_uat.sql
   ```

3. **Create production accounts:**
   - Real admin accounts
   - Real staff accounts
   - Real departments
   - Real products

4. **Archive UAT documents:**
   - Save all UAT documents with date
   - Store screenshots
   - Keep issue log for reference

---

## 🎓 Tips for Successful UAT

### For Testers:
- ✅ Test like a real user (don't try to break it on purpose initially)
- ✅ Report EVERYTHING that seems wrong
- ✅ Take screenshots of errors
- ✅ Write clear steps to reproduce issues
- ✅ Don't assume "it's probably fine" - log it!

### For UAT Coordinator:
- ✅ Keep testing focused and on schedule
- ✅ Prioritize critical issues for immediate fix
- ✅ Don't rush - thorough testing prevents problems later
- ✅ Communicate daily with developers
- ✅ Keep management updated on progress

### For Developers:
- ✅ Fix critical/high issues immediately
- ✅ Provide clear fix notes
- ✅ Test your fix before marking "Fixed"
- ✅ Don't argue if it's "working as designed" - if users find it confusing, it needs improvement

---

## 🎉 You're Ready to Start UAT!

**Follow these 5 steps:**
1. ✅ Load test data
2. ✅ Print documents
3. ✅ Brief team
4. ✅ Assign test cases
5. ✅ Start testing!

**Good luck with your UAT! 🚀**

---

**Document End**
**Generated:** 7 January 2026
**System:** Sistem Pengurusan Bilik Stor dan Inventori MPK

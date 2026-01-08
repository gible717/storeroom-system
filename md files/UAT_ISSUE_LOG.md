# UAT Issue Tracking Log
**Sistem Pengurusan Bilik Stor dan Inventori - MPK**

**Document Version:** 1.0
**Date Created:** 7 January 2026
**UAT Phase:** Issue Tracking

---

## 📋 How to Use This Log

### For Recording Issues:
1. Copy the issue template below
2. Fill in all required fields
3. Assign severity based on definitions
4. Track status until resolution

### For Managing Issues:
- **Daily Review:** Check all open issues
- **Prioritize:** Critical/High issues first
- **Update Status:** As issues are fixed/verified
- **Final Review:** All issues must be closed before UAT sign-off

---

## 🔴 Issue Severity Definitions

| Severity | Definition | Action Required | Examples |
|----------|------------|-----------------|----------|
| **Critical** | System crash, data loss, security vulnerability, core functionality broken | Fix immediately, blocks UAT | Cannot login, stock goes negative, SQL injection |
| **High** | Major feature not working, significant workflow impact, no workaround | Fix before deployment | Approval doesn't update stock, reports crash |
| **Medium** | Feature partially working, minor workflow impact, workaround exists | Fix in next update | Sorting doesn't work, notification delay |
| **Low** | Cosmetic issues, minor inconveniences, easy workaround | Fix when possible | Button alignment, typo in label |

---

## 📊 Issue Summary Dashboard

| Status | Critical | High | Medium | Low | Total |
|--------|----------|------|--------|-----|-------|
| **Open** | 0 | 0 | 0 | 0 | 0 |
| **In Progress** | 0 | 0 | 0 | 0 | 0 |
| **Fixed** | 0 | 0 | 0 | 0 | 0 |
| **Verified** | 0 | 0 | 0 | 0 | 0 |
| **Closed** | 0 | 0 | 0 | 0 | 0 |
| **Total** | 0 | 0 | 0 | 0 | 0 |

**Update this table after each UAT session**

---

## 📝 Issue Log Entries

---

### Issue #001

**Status:** ☐ Open | ☐ In Progress | ☐ Fixed | ☐ Verified | ☐ Closed
**Severity:** ☐ Critical | ☐ High | ☐ Medium | ☐ Low
**Module:** _________________
**Test Case ID:** _________________
**Reported By:** _________________
**Date Reported:** _________________

**Issue Title:**
_________________________________________________________________

**Description:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

**Steps to Reproduce:**
1. _________________________________________________________________
2. _________________________________________________________________
3. _________________________________________________________________

**Expected Result:**
_________________________________________________________________
_________________________________________________________________

**Actual Result:**
_________________________________________________________________
_________________________________________________________________

**Screenshots/Evidence:**
☐ Attached (filename: _________________ )

**Workaround (if any):**
_________________________________________________________________

**Assigned To:** _________________
**Target Fix Date:** _________________
**Fixed Date:** _________________
**Verified By:** _________________
**Verification Date:** _________________

**Resolution Notes:**
_________________________________________________________________
_________________________________________________________________

**Related Issues:** #___, #___

---

### Issue #002

**Status:** ☐ Open | ☐ In Progress | ☐ Fixed | ☐ Verified | ☐ Closed
**Severity:** ☐ Critical | ☐ High | ☐ Medium | ☐ Low
**Module:** _________________
**Test Case ID:** _________________
**Reported By:** _________________
**Date Reported:** _________________

**Issue Title:**
_________________________________________________________________

**Description:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

**Steps to Reproduce:**
1. _________________________________________________________________
2. _________________________________________________________________
3. _________________________________________________________________

**Expected Result:**
_________________________________________________________________
_________________________________________________________________

**Actual Result:**
_________________________________________________________________
_________________________________________________________________

**Screenshots/Evidence:**
☐ Attached (filename: _________________ )

**Workaround (if any):**
_________________________________________________________________

**Assigned To:** _________________
**Target Fix Date:** _________________
**Fixed Date:** _________________
**Verified By:** _________________
**Verification Date:** _________________

**Resolution Notes:**
_________________________________________________________________
_________________________________________________________________

**Related Issues:** #___, #___

---

### Issue #003

**Status:** ☐ Open | ☐ In Progress | ☐ Fixed | ☐ Verified | ☐ Closed
**Severity:** ☐ Critical | ☐ High | ☐ Medium | ☐ Low
**Module:** _________________
**Test Case ID:** _________________
**Reported By:** _________________
**Date Reported:** _________________

**Issue Title:**
_________________________________________________________________

**Description:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

**Steps to Reproduce:**
1. _________________________________________________________________
2. _________________________________________________________________
3. _________________________________________________________________

**Expected Result:**
_________________________________________________________________
_________________________________________________________________

**Actual Result:**
_________________________________________________________________
_________________________________________________________________

**Screenshots/Evidence:**
☐ Attached (filename: _________________ )

**Workaround (if any):**
_________________________________________________________________

**Assigned To:** _________________
**Target Fix Date:** _________________
**Fixed Date:** _________________
**Verified By:** _________________
**Verification Date:** _________________

**Resolution Notes:**
_________________________________________________________________
_________________________________________________________________

**Related Issues:** #___, #___

---

## 📌 Issue Status Workflow

```
[Open] → [In Progress] → [Fixed] → [Verified] → [Closed]
   ↓                          ↓
   └─────── [Rejected] ───────┘
```

### Status Definitions:

- **Open:** Issue reported, awaiting assignment
- **In Progress:** Developer working on fix
- **Fixed:** Fix completed, awaiting testing
- **Verified:** Tester confirmed fix works
- **Closed:** Issue resolved and verified
- **Rejected:** Not an issue / working as designed

---

## 📋 Example Issue (for reference)

### Issue #EX001

**Status:** ✅ Closed
**Severity:** 🔴 Critical
**Module:** Request Management
**Test Case ID:** TC-A-004
**Reported By:** Ahmad Test Staff
**Date Reported:** 07/01/2026

**Issue Title:**
Stock not deducted after approval

**Description:**
When admin approves request, the request status changes to "Diluluskan" but stock levels (baki_semasa) are not being updated. Stock remains at original value.

**Steps to Reproduce:**
1. Login as ADMIN001
2. Review request ID #42 (10 units of Pen Biru requested)
3. Note current stock: 100 units
4. Approve request with full quantity
5. Check product stock in "Urus Produk"
6. Stock still shows 100 units (should be 90)

**Expected Result:**
- Request status = "Diluluskan"
- Stock reduced to 90 units
- Transaction log created

**Actual Result:**
- Request status = "Diluluskan" ✅
- Stock remains 100 units ❌
- Transaction log NOT created ❌

**Screenshots/Evidence:**
✅ Attached (filename: stock_not_updated_20260107.png)

**Workaround (if any):**
Admin must manually adjust stock using "Kemaskini Stok Manual"

**Assigned To:** Developer Team
**Target Fix Date:** 08/01/2026
**Fixed Date:** 08/01/2026
**Verified By:** Admin Test Primary
**Verification Date:** 08/01/2026

**Resolution Notes:**
Missing UPDATE query in request_review_process.php line 158.
Added proper stock deduction logic with transaction logging.
Tested with multiple scenarios - working correctly now.

**Related Issues:** None

---

## 🔍 Common Issues Checklist

Use this as a quick reference for frequently occurring issues:

### Authentication Issues
- [ ] Cannot login with correct credentials
- [ ] Session expires too quickly
- [ ] Logout doesn't clear session
- [ ] First-time password change not enforced

### Stock Management Issues
- [ ] Stock not deducted after approval
- [ ] Negative stock values
- [ ] Stock adjustment doesn't create transaction log
- [ ] Low stock alert not showing

### Request Management Issues
- [ ] Cannot submit request (cart empty error)
- [ ] Request disappears after submission
- [ ] Cannot edit pending request
- [ ] Self-approval not blocked

### Notification Issues
- [ ] Telegram notification not sent
- [ ] Notification shows wrong information
- [ ] Notification failure blocks request submission

### UI/UX Issues
- [ ] Page not mobile responsive
- [ ] Buttons not working
- [ ] Forms not validating input
- [ ] Date format incorrect

### Reporting Issues
- [ ] Report shows wrong data
- [ ] Charts not rendering
- [ ] Export to Excel fails
- [ ] Print layout broken

---

## 📊 Daily UAT Progress Report Template

**Date:** _________________
**Session:** Morning / Afternoon
**Participants:** _________________

### Test Cases Executed Today:
- **Passed:** ___
- **Failed:** ___
- **Blocked:** ___

### New Issues Logged:
- **Critical:** ___
- **High:** ___
- **Medium:** ___
- **Low:** ___

### Issues Resolved Today:
- **Critical:** ___
- **High:** ___
- **Medium:** ___
- **Low:** ___

### Blockers:
_________________________________________________________________
_________________________________________________________________

### Notes/Observations:
_________________________________________________________________
_________________________________________________________________

---

## ⚠️ Critical Issue Escalation

If a **CRITICAL** issue is found:

1. ✅ Log the issue immediately
2. ✅ Mark severity as CRITICAL
3. ✅ Notify UAT Coordinator and IT Manager
4. ✅ Stop testing related functionality
5. ✅ Prioritize fix above all else
6. ✅ Verify fix before resuming UAT

**Critical Issue Contact:**
- **UAT Coordinator:** _________________
- **IT Manager:** _________________
- **Developer Lead:** _________________

---

## 📞 Support Contacts

| Role | Name | Contact |
|------|------|---------|
| UAT Coordinator | | |
| IT Support | | |
| Developer Lead | | |
| System Admin | | |

---

## 📝 Additional Notes

_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

---

**Document End**
**Generated:** 7 January 2026
**System:** Sistem Pengurusan Bilik Stor dan Inventori MPK
**UAT Phase:** Issue Tracking

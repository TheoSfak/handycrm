# Bug Fixing Multi-Agent Workflow

## System Architecture

```
┌─────────────┐
│   Builder   │ Fixes Bug → Reports Fix
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Tester    │ Verifies Fix → Test Report
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Evaluator  │ Judges → Approve/Reject
└──────┬──────┘
       │
       ├─── APPROVED → Next Bug
       └─── REJECTED → Back to Builder
```

---

## Agent Roles & Responsibilities

### 🔨 Builder Agent (Main)
**Responsibilities:**
- Read bug from bugs1.6.0.md
- Implement fix
- Report what was changed
- Provide file paths and line numbers

**Output Format:**
```json
{
  "bug_id": "Issue #3",
  "description": "Deprecated split() in calendar.php",
  "files_modified": ["views/appointments/calendar.php"],
  "changes_made": "Replaced split() with explode()",
  "lines_affected": [361, 362],
  "status": "FIXED",
  "next_action": "REQUEST_TEST"
}
```

---

### 🧪 Tester Agent
**Responsibilities:**
- Verify code changes were applied correctly
- Check syntax validity
- Run applicable tests
- Verify no regressions introduced
- Test actual functionality if possible

**Testing Checklist:**
1. ✅ File exists and is readable
2. ✅ Changes match the reported modifications
3. ✅ No syntax errors (php -l)
4. ✅ Old deprecated code is removed
5. ✅ New code follows best practices
6. ✅ No obvious regressions
7. ✅ Related functionality still works

**Output Format:**
```json
{
  "bug_id": "Issue #3",
  "test_date": "2026-01-21",
  "tests_run": [
    {
      "test": "Code exists check",
      "result": "PASS",
      "details": "File found at correct location"
    },
    {
      "test": "Syntax validation",
      "result": "PASS",
      "details": "php -l returned no errors"
    },
    {
      "test": "Deprecated function removed",
      "result": "PASS",
      "details": "No split() calls found"
    },
    {
      "test": "Replacement correct",
      "result": "PASS",
      "details": "explode() properly implemented"
    }
  ],
  "overall_result": "PASS",
  "confidence": "HIGH",
  "recommendation": "APPROVE"
}
```

---

### ⚖️ Evaluator Agent (Judge)
**Responsibilities:**
- Review Builder's fix report
- Review Tester's test results
- Make final decision
- Provide reasoning
- Authorize next steps

**Evaluation Criteria:**
1. Fix addresses root cause (not just symptoms)
2. All tests pass
3. No new issues introduced
4. Code quality maintained
5. Documentation updated if needed

**Output Format:**
```json
{
  "bug_id": "Issue #3",
  "evaluation_date": "2026-01-21",
  "builder_report": "REVIEWED",
  "tester_report": "REVIEWED",
  "decision": "APPROVED",
  "reasoning": "Fix properly replaces deprecated split() with explode(). All tests pass. No regressions detected.",
  "concerns": [],
  "next_action": "PROCEED_TO_NEXT_BUG",
  "next_bug_id": "Issue #4"
}
```

**Possible Decisions:**
- ✅ **APPROVED** → Move to next bug
- ⚠️ **APPROVED_WITH_NOTES** → Move to next, but note concerns
- 🔄 **NEEDS_REVISION** → Builder must fix issues
- ❌ **REJECTED** → Start over with different approach

---

## Workflow Process

### Phase 1: Fix (Builder)
```bash
1. Builder reads bug from bugs1.6.0.md
2. Builder implements fix
3. Builder generates fix report
4. Builder signals: "FIX_COMPLETE"
```

### Phase 2: Test (Tester)
```bash
1. Tester receives fix report
2. Tester verifies files modified
3. Tester runs validation checks
4. Tester generates test report
5. Tester signals: "TEST_COMPLETE"
```

### Phase 3: Evaluate (Judge)
```bash
1. Evaluator receives both reports
2. Evaluator analyzes fix quality
3. Evaluator checks test coverage
4. Evaluator makes decision
5. Evaluator signals: "APPROVED" or "REJECTED"
```

### Phase 4: Loop or Continue
```bash
IF APPROVED:
  - Log success
  - Move to next bug
  - Repeat from Phase 1

IF REJECTED:
  - Log issues found
  - Return to Builder with feedback
  - Repeat from Phase 1
```

---

## Progress Tracking

### Bug Fixing Log Template

```markdown
## Bug #[ID]: [Description]

**Status:** 🔨 IN_PROGRESS | ✅ COMPLETED | ❌ FAILED  
**Started:** [timestamp]  
**Completed:** [timestamp]

### Builder Report
- Files modified: [list]
- Changes: [description]
- Commit: [if applicable]

### Tester Report
- Tests run: [count]
- Passed: [count]
- Failed: [count]
- Overall: PASS/FAIL

### Evaluator Decision
- Decision: APPROVED/REJECTED
- Reasoning: [text]
- Next: [action]

---
```

---

## Implementation Commands

### Start Bug Fixing Session
```bash
"Start fixing bugs from bugs1.6.0.md using multi-agent workflow"
```

### Fix Specific Bug
```bash
"Fix Issue #3 using builder-tester-evaluator workflow"
```

### Resume After Failure
```bash
"Continue bug fixing from Issue #5"
```

### Check Progress
```bash
"Show bug fixing progress and statistics"
```

---

## File Structure

```
handycrm/
├── bugs1.6.0.md                    # Bug list
├── bug-fixing-workflow.md          # This file (workflow definition)
├── bug-fixing-log.md               # Execution log (to be created)
└── bug-fixing-progress.json        # Progress tracking (to be created)
```

---

## Success Metrics

- **Fix Rate:** % of bugs successfully fixed
- **Test Pass Rate:** % of fixes passing all tests
- **Approval Rate:** % of fixes approved by evaluator
- **Iteration Count:** Average attempts per bug
- **Time per Bug:** Average time to complete cycle

---

## Example Session

```
USER: Start fixing critical bugs
BUILDER: Fixing Issue #3 - deprecated split() in calendar.php
BUILDER: ✅ Fixed! Replaced 2 split() calls with explode()
TESTER: Running tests on calendar.php...
TESTER: ✅ All tests pass! Code validates correctly.
EVALUATOR: Reviewing Issue #3...
EVALUATOR: ✅ APPROVED - Fix is correct, tests pass, moving to Issue #4
BUILDER: Fixing Issue #4 - deprecated split() in maintenances/create.php
...
```

---

**System Status:** ⚙️ READY  
**Last Updated:** January 21, 2026

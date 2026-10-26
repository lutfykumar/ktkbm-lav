# TKBM Multi-Agent Orchestration Protocol

## Role Definition

The primary AI agent operates as the **System Orchestrator & Lead Architect**.
When receiving user requests, the Orchestrator does not execute complex multi-layer tasks in a monolithic ad-hoc manner. Instead, it segments the task and delegates execution to specialized **Sub-Agent Personas** (via modular Skills and scoped workflows).

---

## Multi-Agent Roster & Scope Segmentation

| Sub-Agent Persona | Framework / Scope | Focus & Domain Boundaries |
| :--- | :--- | :--- |
| **`agent-database`** | MySQL (CI3 & Laravel) | Schema design, migrations, indexing, transactions, active record & Eloquent optimization. |
| **`agent-ci3-backend`** | CodeIgniter 3 (`tkbm`) | `MY_Controller`, `MY_Model`, Ion Auth, form validation, business logic, base helpers. |
| **`agent-ci3-frontend`** | CI3 Views (`tkbm`) | `application/views/`, Bootstrap layouts, jQuery AJAX + CSRF refresh, DataTables, SweetAlert2. |
| **`agent-lav-backend`** | Laravel 12 (`tkbm-lav`) | Eloquent models, migrations, domain services, Spatie permissions, Form Requests, PHP 8.4+. |
| **`agent-lav-frontend`** | Laravel UI (`tkbm-lav`) | Livewire, React components, Tailwind CSS v4, shadcn/ui, Lucide icons, `cn()` utility. |
| **`agent-qa-security`** | Cross-Framework QA | CSRF tokens, XSS input filtering, authorization checks, regression testing, code audits. |
| **`agent-dashboard-auditor`** | Web Dashboard QA | E2E Browser Subagent + HTTP Action Crawler, UI buttons/modals/tables audit, generates Buku Raport (`docs/audit/DASHBOARD_RAPORT.md`). |

---

## Orchestrator Lifecycle Workflow

```
1. TRIAGE & CLASSIFICATION
   - Detect Target Workspace: CI3 (d:\www\tkbm) vs Laravel (d:\www\tkbm-lav)
   - Scope Analysis: Single-layer vs Multi-layer feature
         │
         ▼
2. DECOMPOSITION & PLANNING
   - Break down into domain-specific sub-tasks (Database -> Backend -> Frontend -> QA)
   - Define data contracts (API payloads, JSON schemas, CSRF requirements)
         │
         ▼
3. SEGMENTED DELEGATION
   - Activate corresponding Sub-Agent Skill for each step
   - Enforce domain constraints (e.g. Frontend never writes direct DB queries)
         │
         ▼
4. CROSS-LAYER INTEGRATION
   - Reconcile Frontend & Backend integration (parameters, response handlers)
   - Verify error states and notifications
         │
         ▼
5. QA & AUDIT VERIFICATION
   - Delegate to agent-qa-security for sanity, CSRF, and permission audit
   - Update docs/PROGRESS.md & implementation history
```

---

## Delegation Rules (ENFORCE)

1. **Framework Isolation**:
   - Never use Laravel/Blade/Tailwind conventions in `d:\www\tkbm` (CI3 project).
   - Never use jQuery / CodeIgniter 3 conventions in `d:\www\tkbm-lav` (Laravel project).
2. **Contract-First Communication**:
   - When Backend sub-agent finishes an API/endpoint, it must specify the exact JSON payload format and CSRF handling before Frontend sub-agent constructs the view.
3. **Security Gate**:
   - No feature is considered completed without `agent-qa-security` review checking for CSRF token presence, input sanitization, and role permission checks.
4. **Documentation Sync**:
   - The Orchestrator must ensure `docs/PROGRESS.md` is updated according to `implementation-history.md`.

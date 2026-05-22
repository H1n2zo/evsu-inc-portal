# EVSU–OC INC Form Portal — Full Project Structure
**Stack:** Next.js 14 (App Router) · Supabase (Auth + DB + Storage) · Vercel (Hosting)  
**Design:** Minimal White / Maroon / Gold · DM Sans + Playfair Display

---

## Directory Tree

```
evsu-inc-portal/
├── .env.local                         # Supabase keys (never commit)
├── .env.example                       # Safe template to commit
├── .gitignore
├── next.config.js
├── tailwind.config.js
├── tsconfig.json
├── package.json
│
├── supabase/
│   ├── migrations/
│   │   ├── 001_create_users.sql       # profiles, roles, role_assignments
│   │   ├── 002_create_applications.sql # inc_applications, workflow_steps
│   │   ├── 003_create_audit_logs.sql  # audit_logs (append-only)
│   │   └── 004_rls_policies.sql       # Row Level Security for all tables
│   ├── seed.sql                       # Demo users + sample data
│   └── config.toml                    # Supabase local dev config
│
├── src/
│   ├── app/                           # Next.js App Router
│   │   ├── layout.tsx                 # Root layout (fonts, providers)
│   │   ├── page.tsx                   # Landing page (Student / Employee cards)
│   │   │
│   │   ├── (auth)/                    # Auth route group (no sidebar)
│   │   │   ├── login/
│   │   │   │   ├── student/page.tsx   # Student ID + Password login
│   │   │   │   └── employee/page.tsx  # Username + Password login
│   │   │   └── register/page.tsx      # Role-select registration form
│   │   │
│   │   ├── (student)/                 # Student portal route group
│   │   │   ├── layout.tsx             # Student sidebar + nav
│   │   │   ├── dashboard/page.tsx     # Active INC list, status cards
│   │   │   ├── apply/page.tsx         # New INC form (subject picker, fee calc)
│   │   │   ├── applications/
│   │   │   │   ├── page.tsx           # All student applications list
│   │   │   │   └── [id]/page.tsx      # Single application detail + step tracker
│   │   │   └── receipt/[id]/page.tsx  # OR upload page (step 4)
│   │   │
│   │   ├── (instructor)/
│   │   │   ├── layout.tsx
│   │   │   ├── dashboard/page.tsx     # Pending applications in my courses
│   │   │   └── review/[id]/page.tsx   # Grade input + e-signature canvas
│   │   │
│   │   ├── (department-head)/
│   │   │   ├── layout.tsx
│   │   │   ├── dashboard/page.tsx     # Applications from my department
│   │   │   └── review/[id]/page.tsx   # Approve/reject + e-signature canvas
│   │   │
│   │   ├── (registrar)/
│   │   │   ├── layout.tsx
│   │   │   ├── dashboard/page.tsx     # All pending OR verifications
│   │   │   └── verify/[id]/page.tsx   # Split-view OR ledger + confirm grade post
│   │   │
│   │   ├── (admin)/
│   │   │   ├── layout.tsx             # Admin sidebar
│   │   │   ├── dashboard/page.tsx     # Stats, recent activity
│   │   │   ├── users/
│   │   │   │   ├── page.tsx           # User list + role badges
│   │   │   │   └── [id]/page.tsx      # Edit user, assign/remove roles
│   │   │   ├── modules/page.tsx       # Module enable/disable toggles
│   │   │   ├── applications/page.tsx  # All applications (read + override)
│   │   │   └── logs/page.tsx          # Immutable audit log viewer
│   │   │
│   │   └── api/                       # Next.js Route Handlers
│   │       ├── auth/
│   │       │   ├── callback/route.ts  # Supabase OAuth callback
│   │       │   └── signout/route.ts
│   │       ├── applications/
│   │       │   ├── route.ts           # GET list / POST create
│   │       │   └── [id]/
│   │       │       ├── route.ts       # GET single / PATCH update
│   │       │       └── advance/route.ts # POST advance workflow step
│   │       ├── receipts/
│   │       │   └── [id]/route.ts      # POST upload OR image (Supabase Storage)
│   │       ├── signatures/
│   │       │   └── [id]/route.ts      # POST save e-signature image + lock
│   │       ├── roles/
│   │       │   └── route.ts           # GET/POST user role assignments (admin)
│   │       ├── modules/
│   │       │   └── route.ts           # GET/PATCH module toggle state (admin)
│   │       └── notifications/
│   │           └── route.ts           # Internal: trigger PHPMailer-style email
│   │
│   ├── components/
│   │   ├── ui/                        # Reusable design system components
│   │   │   ├── Button.tsx
│   │   │   ├── Input.tsx
│   │   │   ├── Badge.tsx              # Status + role badges
│   │   │   ├── Card.tsx
│   │   │   ├── Toggle.tsx             # Module on/off switch
│   │   │   ├── Modal.tsx
│   │   │   └── Table.tsx
│   │   │
│   │   ├── layout/
│   │   │   ├── StudentSidebar.tsx
│   │   │   ├── AdminSidebar.tsx
│   │   │   ├── EmployeeSidebar.tsx    # Shared: instructor / dept-head / registrar
│   │   │   └── TopBar.tsx
│   │   │
│   │   ├── auth/
│   │   │   ├── LoginCard.tsx          # Shared login card shell
│   │   │   ├── RoleGuard.tsx          # Blocks access if role mismatch
│   │   │   └── RoleChips.tsx          # Multi-role display (maroon/purple/gold chips)
│   │   │
│   │   ├── applications/
│   │   │   ├── StepTracker.tsx        # 7-step visual progress bar
│   │   │   ├── FeeCalculator.tsx      # Units × ₱50 display
│   │   │   ├── ApplicationCard.tsx
│   │   │   └── RejectModal.tsx        # Rejection + reason text
│   │   │
│   │   ├── signature/
│   │   │   └── SignatureCanvas.tsx    # HTML5 canvas draw + lock on submit
│   │   │
│   │   └── receipts/
│   │       ├── ReceiptUpload.tsx      # File picker (.jpg/.png/.pdf, max 5MB)
│   │       └── SplitVerifyPanel.tsx   # Registrar split-view (OR image + text input)
│   │
│   ├── lib/
│   │   ├── supabase/
│   │   │   ├── client.ts              # Browser Supabase client
│   │   │   ├── server.ts              # Server-side Supabase client (cookies)
│   │   │   └── middleware.ts          # Session refresh on every request
│   │   │
│   │   ├── auth/
│   │   │   ├── getRoles.ts            # Fetch current user's assigned roles
│   │   │   ├── hasRole.ts             # Boolean role check helper
│   │   │   └── requireRole.ts        # Server-side redirect if missing role
│   │   │
│   │   ├── workflow/
│   │   │   ├── steps.ts               # Step definitions (1–7), allowed roles per step
│   │   │   ├── advance.ts             # Advance application to next step (with mutex)
│   │   │   └── fees.ts                # Fee calculation: units × 50
│   │   │
│   │   ├── email/
│   │   │   └── sendNotification.ts    # Nodemailer (SMTP) wrapper per workflow event
│   │   │
│   │   ├── pdf/
│   │   │   └── generateINCDoc.ts      # pdf-lib: compile A4 completion document
│   │   │
│   │   └── utils.ts                   # cn(), formatDate(), formatPeso()
│   │
│   ├── hooks/
│   │   ├── useUser.ts                 # Current user + roles from Supabase session
│   │   ├── useApplication.ts          # SWR/TanStack query for single application
│   │   └── useModules.ts              # Fetch enabled module flags
│   │
│   ├── types/
│   │   ├── database.ts                # Supabase generated types (run: supabase gen types)
│   │   ├── roles.ts                   # Role union type: 'admin'|'instructor'|...
│   │   └── workflow.ts                # Step types, application status enums
│   │
│   └── middleware.ts                  # Next.js middleware — session check on protected routes
│
└── public/
    ├── evsu-logo.png
    └── fonts/                         # Self-hosted fallback fonts (optional)
```

---

## Key Files — Code Stubs

### `.env.local`
```env
NEXT_PUBLIC_SUPABASE_URL=https://xxxx.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=eyJ...
SUPABASE_SERVICE_ROLE_KEY=eyJ...        # server-only, never expose to client

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=noreply@evsu.edu.ph
SMTP_PASS=your-google-workspace-app-password

NEXT_PUBLIC_APP_URL=https://evsu-inc.vercel.app
```

---

### `supabase/migrations/001_create_users.sql`
```sql
-- Profiles (extends Supabase auth.users)
create table public.profiles (
  id          uuid primary key references auth.users(id) on delete cascade,
  full_name   text not null,
  student_id  text unique,              -- students only
  username    text unique,              -- employees only
  email       text not null,
  is_active   boolean default false,    -- admin must approve employee accounts
  created_at  timestamptz default now()
);

-- Roles master list
create type public.user_role as enum (
  'admin', 'student', 'instructor', 'department_head', 'registrar'
);

-- Many-to-many: one user can hold multiple roles
create table public.role_assignments (
  id         uuid primary key default gen_random_uuid(),
  user_id    uuid references public.profiles(id) on delete cascade,
  role       public.user_role not null,
  assigned_by uuid references public.profiles(id),
  assigned_at timestamptz default now(),
  unique(user_id, role)
);

-- Module flags (admin-controlled)
create table public.module_flags (
  key         text primary key,         -- e.g. 'inc_filing', 'email_notifications'
  label       text not null,
  enabled     boolean default true,
  updated_by  uuid references public.profiles(id),
  updated_at  timestamptz default now()
);

-- Seed default module flags
insert into public.module_flags (key, label) values
  ('inc_filing',        'INC Form Filing'),
  ('grade_input',       'Grade Input'),
  ('dept_approval',     'Department Head Approval'),
  ('receipt_upload',    'Receipt Upload'),
  ('or_verification',   'OR Verification Panel'),
  ('grade_posting',     'Grade Posting'),
  ('pdf_generation',    'PDF Generation'),
  ('email_notify',      'Email Notifications');
```

---

### `supabase/migrations/002_create_applications.sql`
```sql
create type public.app_status as enum (
  'draft', 'step_1', 'step_2', 'step_3',
  'step_4', 'step_5', 'step_6', 'resolved', 'rejected'
);

create table public.inc_applications (
  id              uuid primary key default gen_random_uuid(),
  student_id      uuid references public.profiles(id) not null,
  subject_code    text not null,
  subject_name    text not null,
  units           int not null check (units > 0),
  processing_fee  int generated always as (units * 50) stored,
  semester        text not null,         -- e.g. '2025-2026-2'
  status          public.app_status default 'step_1',
  is_locked       boolean default false, -- mutex: locked while someone is reviewing
  locked_by       uuid references public.profiles(id),
  locked_at       timestamptz,
  instructor_grade text,
  instructor_sig   text,                 -- storage path to signature image
  dept_head_sig    text,
  registrar_sig    text,
  or_number        text,                 -- typed by registrar
  or_receipt_path  text,                 -- storage path to uploaded OR image
  rejection_note   text,
  pdf_path         text,                 -- storage path to final A4 PDF
  created_at       timestamptz default now(),
  updated_at       timestamptz default now()
);

-- Auto-update updated_at
create or replace function update_updated_at()
returns trigger language plpgsql as $$
begin new.updated_at = now(); return new; end $$;

create trigger trg_applications_updated
  before update on public.inc_applications
  for each row execute function update_updated_at();
```

---

### `supabase/migrations/003_create_audit_logs.sql`
```sql
create table public.audit_logs (
  id          bigserial primary key,
  user_id     uuid references public.profiles(id),
  role        public.user_role,
  action      text not null,            -- e.g. 'grade_posted', 'login', 'module_toggled'
  entity_type text,                     -- 'application' | 'user' | 'module'
  entity_id   uuid,
  description text,
  ip_address  inet,
  created_at  timestamptz default now()
);

-- Prevent any UPDATE or DELETE on audit_logs
create rule no_update_audit as on update to public.audit_logs do instead nothing;
create rule no_delete_audit as on delete to public.audit_logs do instead nothing;
```

---

### `supabase/migrations/004_rls_policies.sql`
```sql
alter table public.profiles        enable row level security;
alter table public.role_assignments enable row level security;
alter table public.inc_applications enable row level security;
alter table public.audit_logs       enable row level security;
alter table public.module_flags     enable row level security;

-- Profiles: users see their own; admin sees all
create policy "profiles_self" on public.profiles
  for select using (auth.uid() = id);

create policy "profiles_admin" on public.profiles
  for all using (
    exists (
      select 1 from public.role_assignments ra
      where ra.user_id = auth.uid() and ra.role = 'admin'
    )
  );

-- Applications: students see only their own
create policy "applications_student_own" on public.inc_applications
  for select using (student_id = auth.uid());

-- Instructors see applications in their courses (simplified: all pending step_2)
create policy "applications_instructor" on public.inc_applications
  for select using (
    status = 'step_2' and
    exists (select 1 from public.role_assignments where user_id = auth.uid() and role = 'instructor')
  );

-- Registrar sees all (step_5, step_6)
create policy "applications_registrar" on public.inc_applications
  for select using (
    exists (select 1 from public.role_assignments where user_id = auth.uid() and role = 'registrar')
  );

-- Admin sees everything
create policy "applications_admin" on public.inc_applications
  for all using (
    exists (select 1 from public.role_assignments where user_id = auth.uid() and role = 'admin')
  );

-- Audit logs: admin read-only
create policy "logs_admin_read" on public.audit_logs
  for select using (
    exists (select 1 from public.role_assignments where user_id = auth.uid() and role = 'admin')
  );

-- Module flags: everyone reads, only admin writes
create policy "modules_read_all"   on public.module_flags for select using (true);
create policy "modules_admin_write" on public.module_flags for all using (
  exists (select 1 from public.role_assignments where user_id = auth.uid() and role = 'admin')
);
```

---

### `src/lib/workflow/steps.ts`
```typescript
export type StepNumber = 1 | 2 | 3 | 4 | 5 | 6 | 7;

export const WORKFLOW_STEPS: Record<StepNumber, {
  label: string;
  actor: 'student' | 'instructor' | 'department_head' | 'registrar';
  description: string;
}> = {
  1: { label: 'Student Files Form',         actor: 'student',         description: 'Student initiates and submits INC application' },
  2: { label: 'Instructor Grade Input',      actor: 'instructor',      description: 'Instructor enters resolved grade and e-signs' },
  3: { label: 'Department Head Approval',    actor: 'department_head', description: 'Dept. head reviews, approves, and e-signs' },
  4: { label: 'Student Pays & Uploads OR',   actor: 'student',         description: 'Student pays processing fee and uploads receipt' },
  5: { label: 'Registrar OR Verification',   actor: 'registrar',       description: 'Registrar verifies OR number against ledger' },
  6: { label: 'Grade Posting',               actor: 'registrar',       description: 'Registrar confirms and posts final grade' },
  7: { label: 'Resolved',                    actor: 'registrar',       description: 'Application archived, PDF generated' },
};

export function getStatusFromStep(step: StepNumber) {
  return `step_${step}` as const;
}
```

---

### `src/lib/auth/getRoles.ts`
```typescript
import { createServerClient } from '@/lib/supabase/server';
import type { UserRole } from '@/types/roles';

export async function getUserRoles(userId: string): Promise<UserRole[]> {
  const supabase = createServerClient();
  const { data } = await supabase
    .from('role_assignments')
    .select('role')
    .eq('user_id', userId);
  return (data ?? []).map(r => r.role as UserRole);
}

// A user can have multiple roles — check if they hold ANY of the required ones
export function hasAnyRole(userRoles: UserRole[], required: UserRole[]): boolean {
  return required.some(r => userRoles.includes(r));
}
```

---

### `src/middleware.ts`
```typescript
import { createMiddlewareClient } from '@supabase/auth-helpers-nextjs';
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const PROTECTED_PREFIXES = ['/dashboard', '/admin', '/instructor', '/registrar', '/department-head'];

export async function middleware(req: NextRequest) {
  const res = NextResponse.next();
  const supabase = createMiddlewareClient({ req, res });

  const { data: { session } } = await supabase.auth.getSession();

  const isProtected = PROTECTED_PREFIXES.some(p => req.nextUrl.pathname.startsWith(p));

  if (isProtected && !session) {
    return NextResponse.redirect(new URL('/login/student', req.url));
  }

  // 30-min idle session check handled by Supabase token refresh
  return res;
}

export const config = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico|api/auth).*)'],
};
```

---

### `src/lib/email/sendNotification.ts`
```typescript
import nodemailer from 'nodemailer';

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST,
  port: Number(process.env.SMTP_PORT),
  secure: false,
  auth: { user: process.env.SMTP_USER, pass: process.env.SMTP_PASS },
});

type NotificationEvent =
  | 'submitted_to_instructor'
  | 'instructor_signed'
  | 'dept_head_approved'
  | 'student_pay_prompt'
  | 'rejected'
  | 'resolved';

const SUBJECTS: Record<NotificationEvent, string> = {
  submitted_to_instructor:  'New INC application pending your review',
  instructor_signed:        'Instructor has signed — awaiting Department Head approval',
  dept_head_approved:       'Approved — please proceed with payment',
  student_pay_prompt:       'Action required: upload your Official Receipt',
  rejected:                 'Your INC application requires attention',
  resolved:                 'Your INC grade has been officially posted',
};

export async function sendNotification(
  event: NotificationEvent,
  to: string,
  context: { studentName: string; subject: string; rejectionNote?: string }
) {
  await transporter.sendMail({
    from: `"EVSU–OC INC Portal" <${process.env.SMTP_USER}>`,
    to,
    subject: SUBJECTS[event],
    html: buildEmailHTML(event, context),
  });
}

function buildEmailHTML(event: NotificationEvent, ctx: typeof context) {
  // Minimal branded HTML email template
  return `
    <div style="font-family:sans-serif;max-width:520px;margin:0 auto;">
      <div style="background:#6B0F1A;padding:20px 24px;">
        <h2 style="color:#C9A84C;margin:0;font-size:18px;">EVSU – Ormoc Campus</h2>
        <p style="color:rgba(255,255,255,0.6);margin:4px 0 0;font-size:12px;">INC Form Portal</p>
      </div>
      <div style="padding:24px;border:1px solid #ddd;border-top:none;">
        <p>Hello,</p>
        <p>${getEmailBody(event, ctx)}</p>
        <p style="margin-top:24px;font-size:12px;color:#999;">
          Eastern Visayas State University – Ormoc Campus
        </p>
      </div>
    </div>
  `;
}

function getEmailBody(event: NotificationEvent, ctx: any) {
  const map: Record<NotificationEvent, string> = {
    submitted_to_instructor: `${ctx.studentName} has submitted an INC form for <strong>${ctx.subject}</strong>. Please log in to enter the resolved grade.`,
    instructor_signed: `The instructor has submitted a grade for <strong>${ctx.subject}</strong>. Please review and sign.`,
    dept_head_approved: `The Department Head has approved your INC application for <strong>${ctx.subject}</strong>. Please proceed to the cashier and upload your Official Receipt.`,
    student_pay_prompt: `Your OR upload is pending for <strong>${ctx.subject}</strong>.`,
    rejected: `Your application for <strong>${ctx.subject}</strong> was rejected.<br><em>${ctx.rejectionNote ?? ''}</em>`,
    resolved: `Your INC grade for <strong>${ctx.subject}</strong> has been officially posted. Your transcript has been updated.`,
  };
  return map[event];
}
```

---

## Deployment Checklist

### Supabase Setup
```bash
# 1. Install Supabase CLI
npm install -g supabase

# 2. Link to your project
supabase login
supabase link --project-ref YOUR_PROJECT_REF

# 3. Run migrations
supabase db push

# 4. Generate TypeScript types
supabase gen types typescript --linked > src/types/database.ts

# 5. Create storage buckets (in Supabase dashboard)
#    - receipts/     → private, max 5MB, allow jpg/png/pdf
#    - signatures/   → private, max 1MB
#    - documents/    → private (final PDFs)
```

### Vercel Setup
```bash
# 1. Install Vercel CLI
npm install -g vercel

# 2. Deploy
vercel --prod

# 3. Set environment variables in Vercel dashboard:
#    NEXT_PUBLIC_SUPABASE_URL
#    NEXT_PUBLIC_SUPABASE_ANON_KEY
#    SUPABASE_SERVICE_ROLE_KEY
#    SMTP_HOST / SMTP_PORT / SMTP_USER / SMTP_PASS
#    NEXT_PUBLIC_APP_URL
```

### package.json (key dependencies)
```json
{
  "dependencies": {
    "next": "14.x",
    "react": "18.x",
    "@supabase/supabase-js": "^2",
    "@supabase/auth-helpers-nextjs": "^0.10",
    "nodemailer": "^6",
    "pdf-lib": "^1.17",
    "tailwindcss": "^3",
    "clsx": "^2",
    "swr": "^2"
  },
  "devDependencies": {
    "typescript": "^5",
    "supabase": "^1",
    "@types/nodemailer": "^6"
  },
  "scripts": {
    "dev": "next dev",
    "build": "next build",
    "db:push": "supabase db push",
    "db:types": "supabase gen types typescript --linked > src/types/database.ts"
  }
}
```

---

## Role-Access Matrix

| Route prefix       | admin | registrar | dept_head | instructor | student |
|--------------------|:-----:|:---------:|:---------:|:----------:|:-------:|
| `/admin/*`         | ✅    | ❌        | ❌        | ❌         | ❌      |
| `/registrar/*`     | ✅    | ✅        | ❌        | ❌         | ❌      |
| `/department-head/*`| ✅   | ❌        | ✅        | ❌         | ❌      |
| `/instructor/*`    | ✅    | ❌        | ✅*       | ✅         | ❌      |
| `/dashboard/*`     | ❌    | ❌        | ❌        | ❌         | ✅      |

> \* A user assigned both `department_head` and `instructor` roles can access instructor routes.  
> Multi-role users (e.g. `registrar` + `instructor`) are redirected to the highest-privilege dashboard on login.

---

*Generated for EVSU–OC INC Form Portal School Project — Barro, Bato-on, Gabor*

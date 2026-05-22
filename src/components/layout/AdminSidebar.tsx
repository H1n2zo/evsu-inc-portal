'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { cn, getInitials } from '@/lib/utils';

interface AdminSidebarProps {
  user: { full_name: string; email: string };
}

const NAV = [
  {
    section: 'Overview',
    items: [
      { href: '/admin/dashboard',     label: 'Dashboard',      icon: GridIcon },
      { href: '/admin/applications',  label: 'Applications',   icon: ClipboardIcon },
    ],
  },
  {
    section: 'Administration',
    items: [
      { href: '/admin/users',    label: 'Users & Roles',   icon: UsersIcon },
      { href: '/admin/modules',  label: 'Module Control',  icon: ModuleIcon },
      { href: '/admin/logs',     label: 'Audit Logs',      icon: LogIcon },
    ],
  },
];

export function AdminSidebar({ user }: AdminSidebarProps) {
  const pathname = usePathname();

  return (
    <aside className="w-60 shrink-0 bg-[#3D080F] flex flex-col min-h-screen">
      {/* Brand */}
      <div className="px-5 pt-5 pb-4 border-b border-white/10">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-full bg-[#C9A84C] flex items-center justify-center text-[#3D080F] font-bold font-serif text-sm shrink-0">
            E
          </div>
          <div>
            <p className="text-white text-[13px] font-semibold font-serif leading-tight">EVSU – Ormoc</p>
            <p className="text-white/40 text-[10.5px] font-light">INC Form Portal</p>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="flex-1 py-3 overflow-y-auto">
        {NAV.map(group => (
          <div key={group.section} className="mb-2">
            <p className="px-5 pt-3 pb-1 text-[9.5px] font-semibold tracking-widest uppercase text-white/30">
              {group.section}
            </p>
            {group.items.map(item => {
              const active = pathname === item.href || pathname.startsWith(item.href + '/');
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={cn(
                    'flex items-center gap-2.5 px-5 py-2.5 text-[13px] font-medium transition-colors',
                    'border-l-[3px]',
                    active
                      ? 'text-[#E8C97A] border-[#C9A84C] bg-[#C9A84C]/10'
                      : 'text-white/55 border-transparent hover:text-white hover:bg-white/5'
                  )}
                >
                  <item.icon className={cn('w-4 h-4 shrink-0', active ? 'opacity-100' : 'opacity-60')} />
                  {item.label}
                </Link>
              );
            })}
          </div>
        ))}
      </nav>

      {/* User footer */}
      <div className="px-4 py-4 border-t border-white/10">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-full bg-[#6B0F1A] border border-[#C9A84C]/50 flex items-center justify-center text-[#E8C97A] text-xs font-semibold shrink-0">
            {getInitials(user.full_name)}
          </div>
          <div className="min-w-0">
            <p className="text-white text-[12px] font-medium truncate">{user.full_name}</p>
            <p className="text-white/40 text-[10.5px] truncate">System Administrator</p>
          </div>
        </div>
        <form action="/api/auth/signout" method="POST">
          <button className="mt-3 w-full text-[11px] text-white/35 hover:text-white/60 transition-colors text-left">
            Sign out →
          </button>
        </form>
      </div>
    </aside>
  );
}

/* ── Inline SVG Icons ── */
function GridIcon({ className }: { className?: string }) {
  return (
    <svg className={className} fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24">
      <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
      <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
    </svg>
  );
}
function ClipboardIcon({ className }: { className?: string }) {
  return (
    <svg className={className} fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24">
      <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
  );
}
function UsersIcon({ className }: { className?: string }) {
  return (
    <svg className={className} fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24">
      <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
      <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
    </svg>
  );
}
function ModuleIcon({ className }: { className?: string }) {
  return (
    <svg className={className} fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24">
      <path d="M12 2l9 4.9V17L12 22 3 17V6.9L12 2z"/><path d="M12 22V12M12 12L3 7M12 12l9-5"/>
    </svg>
  );
}
function LogIcon({ className }: { className?: string }) {
  return (
    <svg className={className} fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24">
      <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
      <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
    </svg>
  );
}

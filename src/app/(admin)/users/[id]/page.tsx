import { createServerClient } from '@/lib/supabase/server';
import { notFound } from 'next/navigation';
import { RoleChip } from '@/components/ui/RoleChip';
import { formatDate, formatDateTime, getInitials } from '@/lib/utils';
import type { UserRole } from '@/types/roles';
import Link from 'next/link';

const ALL_ROLES: UserRole[] = ['admin', 'instructor', 'department_head', 'registrar', 'student'];

export default async function AdminUserDetailPage({ params }: { params: { id: string } }) {
  const supabase = createServerClient();

  const { data: profile } = await supabase
    .from('profiles')
    .select('*, role_assignments(role, assigned_at)')
    .eq('id', params.id)
    .single();

  if (!profile) notFound();

  const currentRoles = (profile.role_assignments ?? []).map((r: { role: UserRole }) => r.role) as UserRole[];

  // Recent activity for this user
  const { data: logs } = await supabase
    .from('audit_logs')
    .select('*')
    .eq('user_id', params.id)
    .order('created_at', { ascending: false })
    .limit(10);

  return (
    <div className="p-8 max-w-4xl">
      {/* Back */}
      <Link href="/admin/users" className="text-xs font-medium text-[#6B0F1A] hover:underline mb-6 inline-block">
        ← Back to Users
      </Link>

      {/* Header */}
      <div className="flex items-center gap-4 mb-8">
        <div className="w-14 h-14 rounded-full bg-[#6B0F1A]/10 flex items-center justify-center text-[#6B0F1A] text-xl font-semibold">
          {getInitials(profile.full_name)}
        </div>
        <div>
          <h1 className="font-serif text-2xl font-semibold text-gray-900">{profile.full_name}</h1>
          <p className="text-sm text-gray-400">{profile.email} · Joined {formatDate(profile.created_at)}</p>
        </div>
        <span className={`ml-auto inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${
          profile.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'
        }`}>
          {profile.is_active ? 'Active' : 'Pending Approval'}
        </span>
      </div>

      <div className="grid grid-cols-2 gap-6">
        {/* Role assignment */}
        <div className="bg-white border border-gray-200 rounded-xl p-5">
          <h2 className="text-sm font-semibold text-gray-900 mb-4">Assigned Roles</h2>
          <p className="text-xs text-gray-400 mb-4 leading-relaxed">
            A user can hold multiple roles simultaneously. For example, a Registrar can also be
            an Instructor. The system resolves permissions from <em>all</em> assigned roles.
          </p>

          {/* Role toggles form */}
          <form action={`/api/roles/${params.id}`} method="POST" className="space-y-3">
            {ALL_ROLES.map(role => {
              const assigned = currentRoles.includes(role);
              return (
                <label key={role} className="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:border-gray-200 cursor-pointer transition-colors">
                  <div className="flex items-center gap-2.5">
                    <RoleChip role={role} />
                    {role === 'admin' && (
                      <span className="text-[10.5px] text-gray-400">Full system access</span>
                    )}
                  </div>
                  <input
                    type="checkbox"
                    name="roles"
                    value={role}
                    defaultChecked={assigned}
                    className="w-4 h-4 accent-[#6B0F1A] cursor-pointer"
                  />
                </label>
              );
            })}
            <button
              type="submit"
              className="w-full mt-2 py-2 bg-[#6B0F1A] text-white text-sm font-medium rounded-lg hover:bg-[#4A0A12] transition-colors"
            >
              Save Role Changes
            </button>
          </form>
        </div>

        {/* Info + recent activity */}
        <div className="space-y-5">
          {/* Account info */}
          <div className="bg-white border border-gray-200 rounded-xl p-5">
            <h2 className="text-sm font-semibold text-gray-900 mb-3">Account Info</h2>
            <dl className="space-y-2.5 text-sm">
              {[
                { label: 'Full Name',   value: profile.full_name },
                { label: 'Email',       value: profile.email },
                { label: 'Username',    value: profile.username ?? '—' },
                { label: 'Student ID',  value: profile.student_id ?? '—' },
                { label: 'Status',      value: profile.is_active ? 'Active' : 'Pending' },
              ].map(({ label, value }) => (
                <div key={label} className="flex justify-between">
                  <dt className="text-gray-400 text-xs">{label}</dt>
                  <dd className="text-gray-900 text-xs font-medium">{value}</dd>
                </div>
              ))}
            </dl>
            {/* Quick actions */}
            <div className="flex gap-2 mt-4 pt-4 border-t border-gray-100">
              {!profile.is_active ? (
                <form action={`/api/users/${params.id}/approve`} method="POST" className="flex-1">
                  <button className="w-full py-1.5 bg-[#6B0F1A] text-white text-xs font-medium rounded-md hover:bg-[#4A0A12]">
                    Approve Account
                  </button>
                </form>
              ) : (
                <form action={`/api/users/${params.id}/disable`} method="POST" className="flex-1">
                  <button className="w-full py-1.5 border border-red-200 text-red-700 text-xs font-medium rounded-md hover:bg-red-50">
                    Disable Account
                  </button>
                </form>
              )}
            </div>
          </div>

          {/* Recent activity */}
          <div className="bg-white border border-gray-200 rounded-xl p-5">
            <h2 className="text-sm font-semibold text-gray-900 mb-3">Recent Activity</h2>
            {logs?.length ? (
              <ul className="space-y-3">
                {logs.map(log => (
                  <li key={log.id} className="text-xs">
                    <p className="text-gray-900">{log.description ?? log.action}</p>
                    <p className="text-gray-400 mt-0.5">{formatDateTime(log.created_at)}</p>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-xs text-gray-400">No activity recorded yet.</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

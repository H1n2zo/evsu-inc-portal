import { createServerClient } from '@/lib/supabase/server';
import { RoleChips } from '@/components/ui/RoleChip';
import { formatDate, getInitials } from '@/lib/utils';
import type { UserRole } from '@/types/roles';
import Link from 'next/link';

export default async function AdminUsersPage({
  searchParams,
}: {
  searchParams: { role?: string; status?: string; q?: string };
}) {
  const supabase = createServerClient();

  // Fetch all profiles with their roles
  const { data: profiles } = await supabase
    .from('profiles')
    .select('*, role_assignments(role)')
    .order('created_at', { ascending: false });

  // Flatten roles onto each profile
  const users = (profiles ?? []).map(p => ({
    ...p,
    roles: (p.role_assignments ?? []).map((r: { role: UserRole }) => r.role) as UserRole[],
  }));

  // Filter by role if provided
  const filtered = users.filter(u => {
    if (searchParams.role && searchParams.role !== 'all' && !u.roles.includes(searchParams.role as UserRole)) return false;
    if (searchParams.status === 'active'  && !u.is_active)  return false;
    if (searchParams.status === 'pending' &&  u.is_active)  return false;
    if (searchParams.q) {
      const q = searchParams.q.toLowerCase();
      if (!u.full_name.toLowerCase().includes(q) && !(u.username ?? '').toLowerCase().includes(q)) return false;
    }
    return true;
  });

  const pending = users.filter(u => !u.is_active).length;

  return (
    <div className="p-8">
      <div className="flex items-start justify-between mb-7">
        <div>
          <h1 className="font-serif text-2xl font-semibold text-gray-900">Users & Roles</h1>
          <p className="text-sm text-gray-400 mt-0.5">
            {pending > 0 && (
              <span className="text-[#6B0F1A] font-medium">{pending} pending approval · </span>
            )}
            {users.length} total accounts
          </p>
        </div>
        <Link
          href="/register"
          className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#6B0F1A] text-white text-xs font-medium rounded-lg hover:bg-[#4A0A12] transition-colors"
        >
          + Add User
        </Link>
      </div>

      {/* Filters */}
      <form className="flex items-center gap-3 mb-5 bg-white border border-gray-200 rounded-xl px-4 py-3">
        <input
          name="q"
          defaultValue={searchParams.q}
          placeholder="Search name or username…"
          className="flex-1 max-w-xs text-sm outline-none placeholder-gray-400"
        />
        <div className="h-4 w-px bg-gray-200" />
        <select
          name="role"
          defaultValue={searchParams.role ?? 'all'}
          className="text-sm text-gray-600 outline-none cursor-pointer bg-transparent"
        >
          <option value="all">All roles</option>
          <option value="admin">Admin</option>
          <option value="instructor">Instructor</option>
          <option value="registrar">Registrar</option>
          <option value="department_head">Department Head</option>
          <option value="student">Student</option>
        </select>
        <select
          name="status"
          defaultValue={searchParams.status ?? 'all'}
          className="text-sm text-gray-600 outline-none cursor-pointer bg-transparent"
        >
          <option value="all">All status</option>
          <option value="active">Active</option>
          <option value="pending">Pending</option>
        </select>
        <button type="submit" className="text-xs font-medium text-[#6B0F1A] hover:underline">
          Filter
        </button>
      </form>

      {/* Table */}
      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-100">
              {['User', 'Identifier', 'Roles', 'Status', 'Joined', 'Actions'].map(h => (
                <th key={h} className="text-left px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {filtered.map(user => (
              <tr key={user.id} className="border-b border-gray-50 hover:bg-gray-50/60 transition-colors">
                {/* User */}
                <td className="px-5 py-3.5">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-[#6B0F1A]/10 flex items-center justify-center text-[#6B0F1A] text-xs font-semibold shrink-0">
                      {getInitials(user.full_name)}
                    </div>
                    <div>
                      <p className="font-medium text-gray-900 text-[13px]">{user.full_name}</p>
                      <p className="text-[11px] text-gray-400">{user.email}</p>
                    </div>
                  </div>
                </td>

                {/* Identifier */}
                <td className="px-5 py-3.5 text-[12px] text-gray-400 font-mono">
                  {user.username ?? user.student_id ?? '—'}
                </td>

                {/* Roles */}
                <td className="px-5 py-3.5">
                  <RoleChips roles={user.roles} />
                </td>

                {/* Status */}
                <td className="px-5 py-3.5">
                  <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium ${
                    user.is_active
                      ? 'bg-green-50 text-green-700'
                      : 'bg-gray-100 text-gray-500'
                  }`}>
                    {user.is_active ? 'Active' : 'Pending'}
                  </span>
                </td>

                {/* Joined */}
                <td className="px-5 py-3.5 text-[12px] text-gray-400">
                  {formatDate(user.created_at)}
                </td>

                {/* Actions */}
                <td className="px-5 py-3.5">
                  <div className="flex items-center gap-2">
                    <Link
                      href={`/admin/users/${user.id}`}
                      className="text-xs font-medium px-2.5 py-1 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                      Edit Roles
                    </Link>
                    {!user.is_active ? (
                      <form action={`/api/users/${user.id}/approve`} method="POST">
                        <button className="text-xs font-medium px-2.5 py-1 rounded-md bg-[#6B0F1A] text-white hover:bg-[#4A0A12] transition-colors">
                          Approve
                        </button>
                      </form>
                    ) : (
                      <form action={`/api/users/${user.id}/disable`} method="POST">
                        <button className="text-xs font-medium px-2.5 py-1 rounded-md border border-red-200 text-red-700 hover:bg-red-50 transition-colors">
                          Disable
                        </button>
                      </form>
                    )}
                  </div>
                </td>
              </tr>
            ))}
            {!filtered.length && (
              <tr>
                <td colSpan={6} className="px-5 py-10 text-center text-sm text-gray-400">
                  No users found.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

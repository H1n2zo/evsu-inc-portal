import { createServerClient } from '@/lib/supabase/server';
import { Badge } from '@/components/ui/Badge';
import { formatDate, formatPeso, STATUS_LABELS, STATUS_BADGE } from '@/lib/utils';
import Link from 'next/link';

async function getStats(supabase: ReturnType<typeof createServerClient>) {
  const [total, pending, resolved, users] = await Promise.all([
    supabase.from('inc_applications').select('id', { count: 'exact', head: true }),
    supabase.from('inc_applications').select('id', { count: 'exact', head: true })
      .in('status', ['step_1','step_2','step_3','step_4','step_5','step_6']),
    supabase.from('inc_applications').select('id', { count: 'exact', head: true })
      .eq('status', 'resolved'),
    supabase.from('profiles').select('id', { count: 'exact', head: true })
      .eq('is_active', true),
  ]);
  return {
    total:    total.count ?? 0,
    pending:  pending.count ?? 0,
    resolved: resolved.count ?? 0,
    users:    users.count ?? 0,
  };
}

export default async function AdminDashboardPage() {
  const supabase = createServerClient();
  const stats = await getStats(supabase);

  const { data: recent } = await supabase
    .from('inc_applications')
    .select('*, student:profiles(full_name, student_id)')
    .order('created_at', { ascending: false })
    .limit(8);

  return (
    <div className="p-8">
      {/* Top bar */}
      <div className="flex items-start justify-between mb-7">
        <div>
          <h1 className="font-serif text-2xl font-semibold text-gray-900">Dashboard</h1>
          <p className="text-sm text-gray-400 mt-0.5">Academic Year 2025–2026, 2nd Semester</p>
        </div>
        <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
          Admin Access
        </span>
      </div>

      {/* Stat cards */}
      <div className="grid grid-cols-4 gap-4 mb-7">
        {[
          { label: 'Total Applications', value: stats.total,    sub: 'This semester',     color: '' },
          { label: 'Pending Review',     value: stats.pending,  sub: 'Awaiting action',   color: 'text-[#6B0F1A]' },
          { label: 'Resolved',           value: stats.resolved, sub: 'Grades posted',     color: 'text-green-700' },
          { label: 'Active Users',       value: stats.users,    sub: 'All roles',         color: '' },
        ].map(s => (
          <div key={s.label} className="bg-white border border-gray-200 rounded-xl p-5">
            <p className="text-[11.5px] font-medium text-gray-400 tracking-wide mb-2">{s.label}</p>
            <p className={`font-serif text-3xl font-semibold leading-none mb-1 ${s.color || 'text-gray-900'}`}>
              {s.value}
            </p>
            <p className="text-[11.5px] text-gray-400">{s.sub}</p>
          </div>
        ))}
      </div>

      {/* Recent applications table */}
      <div className="bg-white border border-gray-200 rounded-xl">
        <div className="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 className="text-sm font-semibold text-gray-900">Recent Applications</h2>
          <Link href="/admin/applications" className="text-xs font-medium text-[#6B0F1A] hover:underline">
            View all →
          </Link>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-100">
                {['Student', 'Subject', 'Units', 'Fee', 'Status', 'Filed'].map(h => (
                  <th key={h} className="text-left px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {(recent ?? []).map(app => (
                <tr key={app.id} className="border-b border-gray-50 hover:bg-gray-50/60 transition-colors">
                  <td className="px-5 py-3.5">
                    <p className="font-medium text-gray-900 text-[13px]">{app.student?.full_name ?? '—'}</p>
                    <p className="text-[11px] text-gray-400">{app.student?.student_id}</p>
                  </td>
                  <td className="px-5 py-3.5 text-[13px] text-gray-700">
                    {app.subject_code} — {app.subject_name}
                  </td>
                  <td className="px-5 py-3.5 text-[13px] text-gray-500">{app.units} units</td>
                  <td className="px-5 py-3.5 text-[13px] text-gray-500">{formatPeso(app.processing_fee)}</td>
                  <td className="px-5 py-3.5">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium ${STATUS_BADGE[app.status]}`}>
                      {STATUS_LABELS[app.status]}
                    </span>
                  </td>
                  <td className="px-5 py-3.5 text-[12px] text-gray-400">{formatDate(app.created_at)}</td>
                </tr>
              ))}
              {!recent?.length && (
                <tr><td colSpan={6} className="px-5 py-8 text-center text-sm text-gray-400">No applications yet.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

import { createServerClient } from '@/lib/supabase/server';
import { formatDate, formatPeso, STATUS_LABELS, STATUS_BADGE } from '@/lib/utils';
import Link from 'next/link';

export default async function AdminApplicationsPage({
  searchParams,
}: {
  searchParams: { status?: string; q?: string };
}) {
  const supabase = createServerClient();

  let query = supabase
    .from('inc_applications')
    .select('*, student:profiles(full_name, student_id)')
    .order('created_at', { ascending: false });

  if (searchParams.status && searchParams.status !== 'all') {
    query = query.eq('status', searchParams.status);
  }

  const { data: applications } = await query;

  const filtered = (applications ?? []).filter(app => {
    if (!searchParams.q) return true;
    const q = searchParams.q.toLowerCase();
    return (
      app.student?.full_name?.toLowerCase().includes(q) ||
      app.subject_code?.toLowerCase().includes(q) ||
      app.subject_name?.toLowerCase().includes(q)
    );
  });

  const statuses = ['all','step_1','step_2','step_3','step_4','step_5','step_6','resolved','rejected'];

  return (
    <div className="p-8">
      <div className="flex items-start justify-between mb-7">
        <div>
          <h1 className="font-serif text-2xl font-semibold text-gray-900">All Applications</h1>
          <p className="text-sm text-gray-400 mt-0.5">{filtered.length} results</p>
        </div>
      </div>

      {/* Filter bar */}
      <form className="flex items-center gap-3 mb-5 bg-white border border-gray-200 rounded-xl px-4 py-3">
        <input
          name="q"
          defaultValue={searchParams.q}
          placeholder="Search student or subject…"
          className="flex-1 max-w-xs text-sm outline-none placeholder-gray-400"
        />
        <div className="h-4 w-px bg-gray-200" />
        <select
          name="status"
          defaultValue={searchParams.status ?? 'all'}
          className="text-sm text-gray-600 outline-none cursor-pointer bg-transparent"
        >
          {statuses.map(s => (
            <option key={s} value={s}>
              {s === 'all' ? 'All statuses' : STATUS_LABELS[s]}
            </option>
          ))}
        </select>
        <button type="submit" className="text-xs font-medium text-[#6B0F1A] hover:underline">Filter</button>
      </form>

      {/* Table */}
      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-100">
              {['App. ID', 'Student', 'Subject', 'Units', 'Fee', 'Step', 'Status', 'Filed'].map(h => (
                <th key={h} className="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {filtered.map(app => (
              <tr key={app.id} className="border-b border-gray-50 hover:bg-gray-50/60 transition-colors">
                <td className="px-4 py-3.5 text-[11px] text-gray-400 font-mono">
                  #{app.id.slice(0, 8).toUpperCase()}
                </td>
                <td className="px-4 py-3.5">
                  <p className="font-medium text-gray-900 text-[13px]">{app.student?.full_name ?? '—'}</p>
                  <p className="text-[11px] text-gray-400">{app.student?.student_id}</p>
                </td>
                <td className="px-4 py-3.5 text-[13px] text-gray-700">
                  <span className="font-medium">{app.subject_code}</span>
                  <span className="text-gray-400"> — {app.subject_name}</span>
                </td>
                <td className="px-4 py-3.5 text-[13px] text-gray-500">{app.units}</td>
                <td className="px-4 py-3.5 text-[13px] text-gray-500">{formatPeso(app.processing_fee)}</td>
                <td className="px-4 py-3.5 text-[12px] text-gray-500">
                  {STATUS_LABELS[app.status]}
                </td>
                <td className="px-4 py-3.5">
                  <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium ${STATUS_BADGE[app.status]}`}>
                    {STATUS_LABELS[app.status]}
                  </span>
                </td>
                <td className="px-4 py-3.5 text-[12px] text-gray-400">{formatDate(app.created_at)}</td>
              </tr>
            ))}
            {!filtered.length && (
              <tr>
                <td colSpan={8} className="px-5 py-12 text-center text-sm text-gray-400">
                  No applications found.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

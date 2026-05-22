import { createServerClient } from '@/lib/supabase/server';
import { RoleChip } from '@/components/ui/RoleChip';
import { formatDateTime } from '@/lib/utils';
import type { UserRole } from '@/types/roles';

export default async function AdminLogsPage({
  searchParams,
}: {
  searchParams: { page?: string };
}) {
  const supabase = createServerClient();
  const page  = Number(searchParams.page ?? 1);
  const limit = 50;
  const from  = (page - 1) * limit;

  const { data: logs, count } = await supabase
    .from('audit_logs')
    .select('*, profile:profiles(full_name, username, student_id)', { count: 'exact' })
    .order('created_at', { ascending: false })
    .range(from, from + limit - 1);

  const totalPages = Math.ceil((count ?? 0) / limit);

  return (
    <div className="p-8">
      <div className="flex items-start justify-between mb-7">
        <div>
          <h1 className="font-serif text-2xl font-semibold text-gray-900">Audit Logs</h1>
          <p className="text-sm text-gray-400 mt-0.5">
            Immutable activity records — {count?.toLocaleString()} entries total
          </p>
        </div>
        <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700">
          Read-Only
        </span>
      </div>

      <div className="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 mb-5 text-xs text-amber-800">
        Audit logs cannot be modified or deleted. All times are in Philippine Standard Time (UTC+8).
      </div>

      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-100">
              {['Timestamp', 'User', 'Role(s)', 'Action', 'Entity', 'IP Address'].map(h => (
                <th key={h} className="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {(logs ?? []).map(log => (
              <tr key={log.id} className="border-b border-gray-50 hover:bg-gray-50/40 transition-colors">
                {/* Timestamp */}
                <td className="px-4 py-3 text-[11.5px] text-gray-400 font-mono whitespace-nowrap">
                  {formatDateTime(log.created_at)}
                </td>

                {/* User */}
                <td className="px-4 py-3">
                  <p className="text-[12.5px] font-medium text-gray-900">
                    {log.profile?.full_name ?? '—'}
                  </p>
                  <p className="text-[10.5px] text-gray-400 font-mono">
                    {log.profile?.username ?? log.profile?.student_id ?? log.user_id?.slice(0, 8)}
                  </p>
                </td>

                {/* Role */}
                <td className="px-4 py-3">
                  {log.role ? (
                    <RoleChip role={log.role as UserRole} />
                  ) : (
                    <span className="text-[11px] text-gray-400">—</span>
                  )}
                </td>

                {/* Action */}
                <td className="px-4 py-3">
                  <p className="text-[12.5px] text-gray-900">{log.description ?? log.action}</p>
                  <p className="text-[10.5px] text-gray-400 font-mono">{log.action}</p>
                </td>

                {/* Entity */}
                <td className="px-4 py-3 text-[11.5px] text-gray-400">
                  {log.entity_type ? (
                    <>
                      <span className="text-gray-600 capitalize">{log.entity_type}</span>
                      {log.entity_id && (
                        <span className="font-mono ml-1">#{log.entity_id.slice(0, 8)}</span>
                      )}
                    </>
                  ) : '—'}
                </td>

                {/* IP */}
                <td className="px-4 py-3 text-[11.5px] text-gray-400 font-mono">
                  {log.ip_address ?? '—'}
                </td>
              </tr>
            ))}
            {!logs?.length && (
              <tr>
                <td colSpan={6} className="px-5 py-12 text-center text-sm text-gray-400">
                  No log entries yet.
                </td>
              </tr>
            )}
          </tbody>
        </table>

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <p className="text-xs text-gray-400">
              Page {page} of {totalPages} · {count} entries
            </p>
            <div className="flex gap-2">
              {page > 1 && (
                <a href={`?page=${page - 1}`} className="text-xs font-medium text-[#6B0F1A] hover:underline">
                  ← Previous
                </a>
              )}
              {page < totalPages && (
                <a href={`?page=${page + 1}`} className="text-xs font-medium text-[#6B0F1A] hover:underline">
                  Next →
                </a>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

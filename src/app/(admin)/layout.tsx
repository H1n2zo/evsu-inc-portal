import { redirect } from 'next/navigation';
import { requireAdmin } from '@/lib/auth';
import { createServerClient } from '@/lib/supabase/server';
import { AdminSidebar } from '@/components/layout/AdminSidebar';

export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  const { session } = await requireAdmin();

  const supabase = createServerClient();
  const { data: profile } = await supabase
    .from('profiles')
    .select('full_name, email')
    .eq('id', session.user.id)
    .single();

  if (!profile) redirect('/login/employee');

  return (
    <div className="flex min-h-screen bg-[#F1EDE4]">
      <AdminSidebar user={profile} />
      <main className="flex-1 overflow-y-auto">
        {children}
      </main>
    </div>
  );
}

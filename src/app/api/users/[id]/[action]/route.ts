import { NextRequest, NextResponse } from 'next/server';
import { createServiceClient } from '@/lib/supabase/server';
import { getSession } from '@/lib/auth';

export async function POST(
  req: NextRequest,
  { params }: { params: { id: string; action: 'approve' | 'disable' } }
) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  const { id, action } = params;
  const service = createServiceClient();

  const isActive = action === 'approve';

  const { error } = await service
    .from('profiles')
    .update({ is_active: isActive })
    .eq('id', id);

  if (error) return NextResponse.json({ error: error.message }, { status: 500 });

  await service.from('audit_logs').insert({
    user_id:     session.user.id,
    role:        'admin',
    action:      isActive ? 'user_approved' : 'user_disabled',
    entity_type: 'user',
    entity_id:   id,
    description: `User account ${isActive ? 'approved' : 'disabled'} by admin`,
    ip_address:  req.headers.get('x-forwarded-for') ?? req.ip,
  });

  // Redirect back to user list
  return NextResponse.redirect(new URL('/admin/users', req.url));
}

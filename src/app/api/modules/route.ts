import { NextRequest, NextResponse } from 'next/server';
import { createServerClient, createServiceClient } from '@/lib/supabase/server';
import { getSession } from '@/lib/auth';

export async function PATCH(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  const { key, enabled } = await req.json();
  if (!key || typeof enabled !== 'boolean') {
    return NextResponse.json({ error: 'Invalid payload' }, { status: 400 });
  }

  const service = createServiceClient();

  // Update module flag
  const { error } = await service
    .from('module_flags')
    .update({ enabled, updated_by: session.user.id, updated_at: new Date().toISOString() })
    .eq('key', key);

  if (error) return NextResponse.json({ error: error.message }, { status: 500 });

  // Write immutable audit log
  await service.from('audit_logs').insert({
    user_id:     session.user.id,
    role:        'admin',
    action:      'module_toggled',
    entity_type: 'module',
    description: `Module "${key}" ${enabled ? 'enabled' : 'disabled'}`,
    ip_address:  req.headers.get('x-forwarded-for') ?? req.ip,
  });

  return NextResponse.json({ ok: true });
}

export async function GET() {
  const supabase = createServerClient();
  const { data } = await supabase.from('module_flags').select('*').order('key');
  return NextResponse.json(data ?? []);
}

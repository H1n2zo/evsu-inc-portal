import { NextRequest, NextResponse } from 'next/server';
import { createServiceClient } from '@/lib/supabase/server';
import { getSession } from '@/lib/auth';
import type { UserRole } from '@/types/roles';

const VALID_ROLES: UserRole[] = ['admin', 'student', 'instructor', 'department_head', 'registrar'];

export async function POST(
  req: NextRequest,
  { params }: { params: { userId: string } }
) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  const formData = await req.formData();
  const newRoles = formData.getAll('roles') as UserRole[];

  // Validate roles
  const valid = newRoles.filter(r => VALID_ROLES.includes(r));

  const service = createServiceClient();

  // Delete all existing roles then re-insert — clean slate approach
  await service.from('role_assignments').delete().eq('user_id', params.userId);

  if (valid.length > 0) {
    await service.from('role_assignments').insert(
      valid.map(role => ({
        user_id:     params.userId,
        role,
        assigned_by: session.user.id,
      }))
    );
  }

  // Audit log
  await service.from('audit_logs').insert({
    user_id:     session.user.id,
    role:        'admin',
    action:      'roles_updated',
    entity_type: 'user',
    entity_id:   params.userId,
    description: `Roles set to: ${valid.join(', ') || 'none'}`,
  });

  return NextResponse.redirect(new URL(`/admin/users/${params.userId}`, req.url));
}

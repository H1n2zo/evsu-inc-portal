import { redirect } from 'next/navigation';
import { createServerClient } from '@/lib/supabase/server';
import type { UserRole } from '@/types/roles';

export async function getSession() {
  const supabase = createServerClient();
  const { data: { session } } = await supabase.auth.getSession();
  return session;
}

export async function getUserRoles(userId: string): Promise<UserRole[]> {
  const supabase = createServerClient();
  const { data } = await supabase
    .from('role_assignments')
    .select('role')
    .eq('user_id', userId);
  return (data ?? []).map((r: { role: UserRole }) => r.role);
}

export function hasRole(userRoles: UserRole[], role: UserRole): boolean {
  return userRoles.includes(role);
}

export function hasAnyRole(userRoles: UserRole[], roles: UserRole[]): boolean {
  return roles.some(r => userRoles.includes(r));
}

/** Server-side guard — call at the top of any admin page */
export async function requireAdmin() {
  const session = await getSession();
  if (!session) redirect('/login/employee');

  const roles = await getUserRoles(session.user.id);
  if (!hasRole(roles, 'admin')) redirect('/login/employee');

  return { session, roles };
}

/** Returns the highest-priority redirect path for a user's roles */
export function getHomeForRoles(roles: UserRole[]): string {
  if (roles.includes('admin'))           return '/admin/dashboard';
  if (roles.includes('registrar'))       return '/registrar/dashboard';
  if (roles.includes('department_head')) return '/department-head/dashboard';
  if (roles.includes('instructor'))      return '/instructor/dashboard';
  if (roles.includes('student'))         return '/dashboard';
  return '/login/student';
}

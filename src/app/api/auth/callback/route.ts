import { NextRequest, NextResponse } from 'next/server';
import { createServerClient } from '@/lib/supabase/server';
import { getUserRoles, getHomeForRoles } from '@/lib/auth';

export async function GET(req: NextRequest) {
  const { searchParams, origin } = new URL(req.url);
  const code = searchParams.get('code');

  if (code) {
    const supabase = createServerClient();
    const { data: { session }, error } = await supabase.auth.exchangeCodeForSession(code);

    if (!error && session) {
      const roles = await getUserRoles(session.user.id);
      const home  = getHomeForRoles(roles);
      return NextResponse.redirect(`${origin}${home}`);
    }
  }

  return NextResponse.redirect(`${origin}/login/employee?error=auth`);
}

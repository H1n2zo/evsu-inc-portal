import { ROLE_LABELS, ROLE_COLORS, type UserRole } from '@/types/roles';
import { cn } from '@/lib/utils';

interface RoleChipProps {
  role: UserRole;
  className?: string;
}

export function RoleChip({ role, className }: RoleChipProps) {
  const { bg, text } = ROLE_COLORS[role];
  return (
    <span className={cn(
      'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium',
      bg, text, className
    )}>
      {ROLE_LABELS[role]}
    </span>
  );
}

interface RoleChipsProps {
  roles: UserRole[];
}

export function RoleChips({ roles }: RoleChipsProps) {
  return (
    <div className="flex flex-wrap gap-1">
      {roles.map(role => <RoleChip key={role} role={role} />)}
    </div>
  );
}

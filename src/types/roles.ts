export type UserRole =
  | 'admin'
  | 'student'
  | 'instructor'
  | 'department_head'
  | 'registrar';

export const ROLE_LABELS: Record<UserRole, string> = {
  admin: 'Administrator',
  student: 'Student',
  instructor: 'Instructor',
  department_head: 'Department Head',
  registrar: 'Registrar',
};

export const ROLE_COLORS: Record<UserRole, { bg: string; text: string }> = {
  admin:           { bg: 'bg-amber-100',   text: 'text-amber-800' },
  student:         { bg: 'bg-green-100',   text: 'text-green-800' },
  instructor:      { bg: 'bg-blue-100',    text: 'text-blue-800' },
  department_head: { bg: 'bg-purple-100',  text: 'text-purple-800' },
  registrar:       { bg: 'bg-rose-100',    text: 'text-rose-800' },
};

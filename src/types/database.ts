export type AppStatus =
  | 'draft'
  | 'step_1'
  | 'step_2'
  | 'step_3'
  | 'step_4'
  | 'step_5'
  | 'step_6'
  | 'resolved'
  | 'rejected';

export interface Profile {
  id: string;
  full_name: string;
  student_id?: string | null;
  username?: string | null;
  email: string;
  is_active: boolean;
  created_at: string;
  roles?: import('./roles').UserRole[];
}

export interface RoleAssignment {
  id: string;
  user_id: string;
  role: import('./roles').UserRole;
  assigned_by?: string | null;
  assigned_at: string;
}

export interface IncApplication {
  id: string;
  student_id: string;
  subject_code: string;
  subject_name: string;
  units: number;
  processing_fee: number;
  semester: string;
  status: AppStatus;
  is_locked: boolean;
  instructor_grade?: string | null;
  or_number?: string | null;
  or_receipt_path?: string | null;
  rejection_note?: string | null;
  pdf_path?: string | null;
  created_at: string;
  updated_at: string;
  student?: Profile;
}

export interface ModuleFlag {
  key: string;
  label: string;
  enabled: boolean;
  updated_at: string;
  updated_by?: string | null;
}

export interface AuditLog {
  id: number;
  user_id?: string | null;
  role?: import('./roles').UserRole | null;
  action: string;
  entity_type?: string | null;
  entity_id?: string | null;
  description?: string | null;
  ip_address?: string | null;
  created_at: string;
  profile?: Pick<Profile, 'full_name' | 'username' | 'student_id'>;
}

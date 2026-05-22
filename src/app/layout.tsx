import type { Metadata } from 'next';
import { DM_Sans, Playfair_Display } from 'next/font/google';
import './globals.css';

const dmSans = DM_Sans({
  subsets: ['latin'],
  variable: '--font-dm-sans',
  weight: ['300', '400', '500', '600'],
});

const playfair = Playfair_Display({
  subsets: ['latin'],
  variable: '--font-playfair',
  weight: ['600', '700'],
});

export const metadata: Metadata = {
  title: 'EVSU – Ormoc Campus | INC Form Portal',
  description: 'Online INC Form Completion System — Eastern Visayas State University Ormoc Campus',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${dmSans.variable} ${playfair.variable}`}>
      <body className="font-sans antialiased bg-[#F8F5EE] text-gray-900">
        {children}
      </body>
    </html>
  );
}

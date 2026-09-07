import type { Metadata, Viewport } from "next";
import "./globals.css";
import { AppProviders } from "@/providers/app-providers";

const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? "";

export const metadata: Metadata = {
  title: "QA Management System",
  description: "Manufacturing quality management workspace",
  icons: { icon: `${basePath}/icons/qa-logo-192.png` },
};
export const viewport: Viewport = { width: "device-width", initialScale: 1, themeColor: "#b91c1c" };

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="en" className="h-full antialiased" suppressHydrationWarning>
      <body className="min-h-full flex flex-col"><AppProviders>{children}</AppProviders></body>
    </html>
  );
}

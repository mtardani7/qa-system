import type { Metadata, Viewport } from "next";
import "./globals.css";
import { AppProviders } from "@/providers/app-providers";

export const metadata: Metadata = {
  title: "QA Management System",
  description: "Manufacturing quality management workspace",
};
export const viewport: Viewport = { width: "device-width", initialScale: 1, themeColor: "#b91c1c" };

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="en" className="h-full antialiased" suppressHydrationWarning>
      <body className="min-h-full flex flex-col"><AppProviders>{children}</AppProviders></body>
    </html>
  );
}

import type { Metadata } from "next";
import { DocsLayout } from "@/components/docs/docs-layout";
export const metadata: Metadata = { title: { default: "Documentation | QA Management System", template: "%s | QA Management System" }, description: "Public documentation for QA Management System." };
export default function Layout({ children }: { children: React.ReactNode }) { return <DocsLayout>{children}</DocsLayout>; }

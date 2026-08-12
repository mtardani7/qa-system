import type { Metadata } from "next";
import { LandingPage } from "@/features/public/components/landing-page";
export const metadata: Metadata = { title: "QA Management System | Enterprise Quality Assurance", description: "Enterprise quality assurance system for manufacturing production, inspection, reporting, and management insight." };
export default function Home() { return <LandingPage />; }

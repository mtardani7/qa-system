"use client";

import { useParams, useRouter } from "next/navigation";
import { DailyReportFormDialog } from "@/features/daily-reports/components/daily-report-form-dialog";
import { useDailyReport } from "@/features/daily-reports/hooks";
import { PageSkeleton } from "@/components/layouts/page-skeleton";

export default function DailyReportEditPage() { const params = useParams<{ id: string }>(); const router = useRouter(); const { data: report, isLoading } = useDailyReport(Number(params.id)); if (isLoading) return <PageSkeleton variant="detail" />; return <DailyReportFormDialog open report={report} onClose={() => router.push(`/daily-reports/${params.id}`)} />; }

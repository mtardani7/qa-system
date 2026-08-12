import { Skeleton } from "@/components/ui/skeleton";

export function PageSkeleton({ variant = "page" }: { variant?: "page" | "table" | "detail" }) {
  if (variant === "table") {
    return (
      <div className="space-y-4 p-5" aria-label="Loading content">
        <div className="flex items-center justify-between gap-4"><Skeleton className="h-6 w-48" /><Skeleton className="h-10 w-56" /></div>
        <Skeleton className="h-12 w-full" />
        {Array.from({ length: 7 }, (_, index) => <Skeleton key={index} className="h-12 w-full" />)}
      </div>
    );
  }

  if (variant === "detail") {
    return (
      <div className="space-y-6 p-6 lg:p-10" aria-label="Loading content">
        <div className="flex items-end justify-between gap-4"><div className="space-y-3"><Skeleton className="h-5 w-36" /><Skeleton className="h-9 w-64" /><Skeleton className="h-4 w-44" /></div><Skeleton className="h-10 w-52" /></div>
        <Skeleton className="h-[28rem] w-full" />
      </div>
    );
  }

  return (
    <div className="space-y-6 p-6 lg:p-10" aria-label="Loading content">
      <Skeleton className="h-8 w-64" />
      <Skeleton className="h-20 w-full" />
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">{Array.from({ length: 4 }, (_, index) => <Skeleton key={index} className="h-28" />)}</div>
      <Skeleton className="h-96 w-full" />
    </div>
  );
}
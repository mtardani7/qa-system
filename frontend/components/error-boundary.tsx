"use client";

import { Component, type ErrorInfo, type ReactNode } from "react";
type Props = { children: ReactNode; fallback?: ReactNode }; type State = { hasError: boolean };
export class AppErrorBoundary extends Component<Props, State> { state: State = { hasError: false }; static getDerivedStateFromError(): State { return { hasError: true }; } componentDidCatch(error: Error, info: ErrorInfo) { console.error("Unhandled application error", error, info); } render() { return this.state.hasError ? this.props.fallback ?? <div className="p-6 text-sm text-rose-600">Something went wrong.</div> : this.props.children; } }

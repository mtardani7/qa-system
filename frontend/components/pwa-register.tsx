"use client";

import { useEffect } from "react";

const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? "";

export function PwaRegister() {
	useEffect(() => {
		if (process.env.NODE_ENV !== "production" || !("serviceWorker" in navigator)) return;

		const scope = `${basePath || ""}/`.replace(/\/+/g, "/");
		const scriptUrl = `${scope}sw.js`;
		navigator.serviceWorker.register(scriptUrl, { scope }).catch((error) => console.warn("PWA registration failed", error));
	}, []);

	return null;
}

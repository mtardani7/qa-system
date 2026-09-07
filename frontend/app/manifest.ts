import type { MetadataRoute } from "next";

export default function manifest(): MetadataRoute.Manifest {
	const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? "";

	return {
		name: "QA Management System",
		short_name: "QA System",
		description: "Manufacturing quality management workspace",
		start_url: `${basePath}/dashboard`,
		display: "standalone",
		background_color: "#f8fafc",
		theme_color: "#b91c1c",
		icons: [
			{ src: `${basePath}/icons/qa-logo-192.png`, sizes: "192x192", type: "image/png" },
			{ src: `${basePath}/icons/qa-logo-512.png`, sizes: "512x512", type: "image/png" },
		],
	};
}

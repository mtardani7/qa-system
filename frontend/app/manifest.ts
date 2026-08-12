import type { MetadataRoute } from "next";
export default function manifest(): MetadataRoute.Manifest { return { name: "QA Management System", short_name: "QA System", description: "Manufacturing quality management workspace", start_url: "/dashboard", display: "standalone", background_color: "#f8fafc", theme_color: "#059669", icons: [] }; }

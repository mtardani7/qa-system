"use client";

import { useEffect, useState } from "react";
export function useMobile(breakpoint = 1024) { const [mobile, setMobile] = useState(false); useEffect(() => { const media = window.matchMedia(`(max-width: ${breakpoint - 1}px)`); const update = () => setMobile(media.matches); update(); media.addEventListener("change", update); return () => media.removeEventListener("change", update); }, [breakpoint]); return mobile; }

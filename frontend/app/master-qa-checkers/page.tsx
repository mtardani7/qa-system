import { MasterDataPage } from "@/features/master-data/components/master-data-page";
import { masterConfigs } from "@/features/master-data/types";
export default function Page() { return <MasterDataPage config={masterConfigs["qa-checkers"]} />; }

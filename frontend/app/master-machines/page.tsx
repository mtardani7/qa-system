import { MasterDataPage } from "@/features/master-data/components/master-data-page";
import { MachineImportActions } from "@/features/master-data/components/machine-import-dialog";
import { masterConfigs } from "@/features/master-data/types";
export default function Page() { return <><MasterDataPage config={masterConfigs.machines} /><MachineImportActions /></>; }

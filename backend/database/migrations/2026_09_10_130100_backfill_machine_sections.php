<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Section mapping derived from the W36 weekly report template.
     * Machines with ambiguous names (e.g. "Siden #1", "Super #1/#2") are left
     * unassigned so an operator can classify them later via master data.
     */
    private array $sectionByCode = [
        'M01' => 'EXTRUSION', 'M02' => 'EXTRUSION', 'M30' => 'EXTRUSION',
        'M03' => 'HEADING', 'M04' => 'HEADING', 'M05' => 'HEADING', 'M06' => 'HEADING',
        'M07' => 'HEADING', 'M08' => 'HEADING', 'M26' => 'HEADING', 'M32' => 'HEADING',
        'M09' => 'DECORATION', 'M10' => 'DECORATION', 'M11' => 'DECORATION', 'M12' => 'DECORATION',
        'M13' => 'DECORATION', 'M14' => 'DECORATION', 'M22' => 'DECORATION', 'M23' => 'DECORATION',
        'M28' => 'DECORATION', 'M29' => 'DECORATION', 'M31' => 'DECORATION',
        'M15' => 'SET', 'M16' => 'SET', 'M17' => 'SET', 'M18' => 'SET', 'M19' => 'SET',
        'M20' => 'SET', 'M21' => 'SET', 'M24' => 'SET', 'M25' => 'SET', 'M27' => 'SET', 'M33' => 'SET',
    ];

    public function up(): void
    {
        foreach ($this->sectionByCode as $code => $section) {
            DB::table('machines')->where('code', $code)->update(['section' => $section]);
        }
    }

    public function down(): void
    {
        DB::table('machines')->whereIn('code', array_keys($this->sectionByCode))->update(['section' => null]);
    }
};

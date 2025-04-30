 <?php
usar Illuminate\Database\Migrations\Migration;
usar Illuminate\Database\Schema\Blueprint;
usar Illuminate\Support\Facades\Schema;
// Nome da classe deve corresponder ao nome do arquivo da migração
retornar nova classe estende Migração // Ex: AddSoftDeletesToAssetsTable
{
função pública up(): void {
Esquema::table('assets', função (Blueprint $table) {
se (!Esquema::hasColumn('assets', 'deleted_at')) {
$table->softDeletes()->after('updated_at');
}
});
}
função pública down(): void {
if (Schema::hasColumn('assets', 'deleted_at')) {
Esquema::table('assets', função (Blueprint $table) {
$table->dropSoftDeletes();
});
}
}
};
'''IMplementar   


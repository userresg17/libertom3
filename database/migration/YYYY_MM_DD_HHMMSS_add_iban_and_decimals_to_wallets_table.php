use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nome da classe deve corresponder ao nome do arquivo da migration
return new class extends Migration // Ex: AddIbanAndDecimalsToWalletsTable
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Adiciona iban se não existir (e define como unique)
            if (!Schema::hasColumn('wallets', 'iban')) {
                // Garanta que a coluna esteja vazia ou limpa antes de rodar se já existirem dados
                 $table->string('iban')->nullable()->unique()->after('balance');
            }
            // Adiciona decimal_places
             if (!Schema::hasColumn('wallets', 'decimal_places')) {
                 $table->tinyInteger('decimal_places')->unsigned()->default(2)->after('iban');
             }

             // Adiciona softDeletes se ainda não existir
             if (!Schema::hasColumn('wallets', 'deleted_at')) {
                 $table->softDeletes()->after('updated_at');
             }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // Verifica existência antes de remover
         if (Schema::hasColumn('wallets', 'iban')) {
             Schema::table('wallets', function (Blueprint $table) {
                // Precisa remover o índice unique antes de remover a coluna
                // $table->dropUnique('wallets_iban_unique'); // Nome padrão do índice unique
                $table->dropColumn('iban');
             });
         }
         if (Schema::hasColumn('wallets', 'decimal_places')) {
             Schema::table('wallets', function (Blueprint $table) {
                $table->dropColumn('decimal_places');
             });
         }
         if (Schema::hasColumn('wallets', 'deleted_at')) {
             Schema::table('wallets', function (Blueprint $table) {
                $table->dropSoftDeletes();
             });
         }
    }
};
```
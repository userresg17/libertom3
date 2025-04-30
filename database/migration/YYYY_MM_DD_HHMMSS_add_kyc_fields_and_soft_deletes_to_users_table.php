use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nome da classe deve corresponder ao nome do arquivo da migration
return new class extends Migration // Ex: AddKycFieldsAndSoftDeletesToUsersTable
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adiciona campos após 'transaction_password' ou outro campo existente relevante
            // Verifique se 'transaction_password' já existe da migration anterior
            if (Schema::hasColumn('users', 'transaction_password')) {
                $table->string('phone_number', 30)->nullable()->after('transaction_password'); // Aumentei o tamanho
                $table->date('date_of_birth')->nullable()->after('phone_number');
                $table->text('address')->nullable()->after('date_of_birth');
            } else {
                // Caso transaction_password não exista (improvável, mas seguro)
                $table->string('phone_number', 30)->nullable()->after('password');
                $table->date('date_of_birth')->nullable()->after('phone_number');
                $table->text('address')->nullable()->after('date_of_birth');
            }

            // Adiciona softDeletes se ainda não existir
            if (!Schema::hasColumn('users', 'deleted_at')) {
                 $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verifica se as colunas existem antes de tentar removê-las
        if (Schema::hasColumns('users', ['phone_number', 'date_of_birth', 'address'])) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['phone_number', 'date_of_birth', 'address']);
            });
        }
         // Remove softDeletes apenas se a coluna existir
         if (Schema::hasColumn('users', 'deleted_at')) {
             Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
             });
         }
    }
};
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config; // <-- Importar Config
// use App\Models\Setting; // <-- Importar Setting SE for ler direto do BD (não recomendado aqui)

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard'; // Rota home do cliente padrão
    public const ADMIN_HOME = '/admin/dashboard'; // Rota home do admin padrão (ajustar prefixo se necessário)

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Carrega rotas de API
            Route::middleware('api')
                ->prefix('api') // Prefixo padrão /api
                ->group(base_path('routes/api.php'));

            // Carrega rotas Web (Cliente, Auth padrão)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // --- Carrega rotas do Admin ---
            $this->mapAdminRoutes(); // Chama método separado para organizar
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Adicionar limitadores específicos para admin se necessário
        // RateLimiter::for('admin_api', function (Request $request) { ... });
    }

    /**
     * Define the "admin" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAdminRoutes(): void
    {
        // 1. Obter o prefixo (ler da config é mais seguro no boot)
        // $adminPrefix = Setting::getValue('admin_route_prefix', 'administracao'); // Ler do BD - CUIDADO com dependências
        $adminPrefix = Config::get('app.admin_route_prefix', 'administracao'); // Ler da Config (recomendado)

        // 2. Definir o grupo de middleware admin
        // Ajuste conforme suas necessidades (ex: adicionar permissões Spatie)
        $adminMiddleware = [
            'web', // Rotas admin geralmente usam o grupo web para sessões, CSRF
            'auth:admin', // Especifica o guard de autenticação 'admin'
            // 'can:access_admin_panel' // Exemplo de permissão Spatie
        ];

        // 3. Carregar as rotas admin com prefixo e middleware
        Route::prefix($adminPrefix)
             ->middleware($adminMiddleware)
             ->name('admin.') // Adiciona 'admin.' ao nome de todas as rotas neste grupo
             ->group(base_path('routes/admin.php'));
    }
}
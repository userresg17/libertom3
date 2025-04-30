<x-guest-layout>
    <h2 class="mb-6 text-2xl font-semibold text-center text-gray-100">Acesse sua Conta</h2>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input.label for="email" value="Email" />
            <x-input.text id="email" class="block w-full mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input.error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input.label for="password" value="Senha" />
            <x-input.text id="password" class="block w-full mt-1"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input.error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="text-amber-500 bg-gray-700 border-gray-600 rounded shadow-sm focus:ring-amber-500 focus:ring-offset-gray-800" name="remember">
                <span class="ml-2 text-sm text-gray-400">Lembrar-me</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
             <div>
                @if (Route::has('password.request'))
                    <a class="text-sm text-gray-400 underline rounded-md hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                        Esqueceu sua senha?
                    </a>
                @endif
             </div>

            <x-button.primary type="submit">
                Entrar
            </x-button.primary>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Não tem uma conta?
                <a href="{{ route('register') }}" class="font-medium text-amber-400 hover:underline">
                    Crie uma agora
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
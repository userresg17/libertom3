<x-guest-layout>
    <h2 class="mb-6 text-2xl font-semibold text-center text-gray-100">Crie sua Conta Libertom</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input.label for="name" value="Nome Completo" />
            <x-input.text id="name" class="block w-full mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input.error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input.label for="email" value="Email" />
            <x-input.text id="email" class="block w-full mt-1" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input.error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input.label for="password" value="Senha" />
            <x-input.text id="password" class="block w-full mt-1"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input.error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input.label for="password_confirmation" value="Confirme a Senha" />
            <x-input.text id="password_confirmation" class="block w-full mt-1"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input.error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Terms and Conditions Agreement (Example) --}}
         <div class="mt-4">
             <x-input.checkbox id="terms" name="terms" required>
                 Eu li e concordo com os <a href="/termos-de-uso" target="_blank" class="underline text-amber-400 hover:text-amber-300">Termos de Uso</a> e a <a href="/politica-de-privacidade" target="_blank" class="underline text-amber-400 hover:text-amber-300">Política de Privacidade</a>.
             </x-input.checkbox>
             <x-input.error :messages="$errors->get('terms')" class="mt-2" />
         </div>

        <div class="flex items-center justify-end mt-6">
            <a class="text-sm text-gray-400 underline rounded-md hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 focus:ring-offset-gray-800" href="{{ route('login') }}">
                Já possui uma conta?
            </a>

            <x-button.primary type="submit" class="ml-4">
                Registrar
            </x-button.primary>
        </div>
    </form>
</x-guest-layout>
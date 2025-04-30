<x-guest-layout>
    <h2 class="mb-4 text-xl font-semibold text-center text-gray-100">Esqueceu sua Senha?</h2>

    <div class="mb-4 text-sm text-gray-400">
        Sem problemas. Informe seu endereço de e-mail e enviaremos um link para redefinição de senha que permitirá que você escolha uma nova.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input.label for="email" value="Email" />
            <x-input.text id="email" class="block w-full mt-1" type="email" name="email" :value="old('email')" required autofocus />
            <x-input.error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-button.primary type="submit">
                Enviar Link de Redefinição
            </x-button.primary>
        </div>
    </form>
</x-guest-layout>
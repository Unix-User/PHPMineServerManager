@component('mail::message', ['theme' => 'default'])
# Convite para participar do time {{ $invitation->team->name }}

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
Você ainda não possui uma conta? Crie uma agora mesmo clicando no botão abaixo. Após criar sua conta, você poderá aceitar o convite para o time clicando no botão de aceitação de convite neste e-mail.

@component('mail::button', ['url' => route('register')])
Criar Conta
@endcomponent

Já possui uma conta? Aceite este convite clicando no botão abaixo:

@else
Aceite este convite clicando no botão abaixo:
@endif

@component('mail::button', ['url' => $acceptUrl])
Aceitar Convite
@endcomponent

Caso não estivesse esperando por um convite para este time, sinta-se livre para ignorar este e-mail.
@endcomponent

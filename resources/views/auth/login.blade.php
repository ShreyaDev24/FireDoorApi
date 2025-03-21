<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo />
        </x-slot>
 
        <x-jet-validation-errors class="mb-4" />
 
        @if (session('message'))
            <div class="mb-4 font-medium text-sm text-info-600">
                {{ session('message') }}
            </div>
        @endif
 
        <form method="POST" action="{{ route('login') }}">
            @csrf
 
            <div>
                <x-jet-label value="{{ __('Email') }}" />
                <x-jet-input class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>
 
            <div class="mt-4">
                <x-jet-label value="{{ __('Password') }}" />
                <x-jet-input class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>
 
            <div class="block mt-4">
                <label class="flex items-center">
                    <input type="checkbox" class="form-checkbox" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>
 
            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
 
                <x-jet-button class="ml-4">
                    {{ __('Login') }}
                </x-jet-button>
 
                <a href="{{ url('auth/google') }}" style="margin-top: 0px !important;background: green;color: #ffffff;padding: 5px;border-radius:7px;" class="ml-2">
                  <strong>Google Login</strong>
                </a> 

                <a href="{{ url('auth/facebook') }}" class="btn btn-lg btn-primary btn-block">
                    <strong>Login With Facebook</strong>
                </a> 
                
                
            </div>
        </form>
    </x-jet-authentication-card>
</x-guest-layout>
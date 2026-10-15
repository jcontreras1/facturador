<x-app-layout>
    <div class="container">
        <x-title title="Perfil" />

        <div class="row g-3">
            <div class="col-12 col-xl-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="col-12 col-xl-6">
                @include('profile.partials.update-password-form')
            </div>

            <div class="col-12">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>

<nav class="navbar navbar-expand-sm" style="background-color: rgb(31, 41, 55); border-bottom: 1px solid rgb(55, 65, 81);">
    <div class="container-fluid px-4">

        {{-- Логотип --}}
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo style="height: 36px; width: auto; fill: #e5e7eb;" />
        </a>

        {{-- Ссылки --}}
        <div class="me-auto">
            <a href="{{ route('dashboard') }}"
               class="btn btn-sm {{ request()->routeIs('dashboard') ? 'text-white' : 'text-secondary' }}"
               style="font-size: 0.9rem;">
                Dashboard
            </a>
        </div>

        {{-- Дропдаун пользователя --}}
        <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2"
                    style="color: #e5e7eb; background: transparent; border: 1px solid rgb(55,65,81);"
                    type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
                {{ Auth::user()->name }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end"
                style="background-color: rgb(31,41,55); border: 1px solid rgb(55,65,81);">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}"
                       style="color: #e5e7eb;">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider" style="border-color: rgb(55,65,81);"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item" style="color: #f87171;">
                            <i class="bi bi-box-arrow-right me-2"></i>Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </div>
</nav>
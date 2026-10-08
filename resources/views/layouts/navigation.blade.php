@php
    $isAdmin = auth()->user()->isAdmin();
    $linkClass = function (bool $active) {
        return $active
            ? 'flex items-center gap-3 px-3 py-2 rounded-lg bg-amber-500 text-slate-900 font-semibold'
            : 'flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white';
    };
@endphp

<aside x-cloak x-show="sidebarOpen || window.innerWidth >= 1024"
       x-transition:enter="transition ease-out duration-300 transform"
       x-transition:enter-start="-translate-x-full opacity-0"
       x-transition:enter-end="translate-x-0 opacity-100"
       x-transition:leave="transition ease-in duration-200 transform"
       x-transition:leave-start="translate-x-0 opacity-100"
       x-transition:leave-end="-translate-x-full opacity-0"
       class="fixed lg:sticky lg:top-0 lg:bottom-auto lg:h-screen inset-y-0 left-0 z-40 w-64 shrink-0 bg-slate-900 text-slate-100 flex flex-col"
       @click.outside="sidebarOpen = false">
    <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-800">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 text-slate-900 font-bold">YB</span>
        <span class="font-semibold text-lg truncate">{{ $shopName }}</span>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">
        <a href="{{ route('dashboard') }}" class="{{ $linkClass(request()->routeIs('dashboard')) }}">
            <x-icon name="home" class="h-5 w-5" />
            Inicio
        </a>

        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wider text-slate-500">Ventas</p>
        <a href="{{ route('sales.create-service') }}" class="{{ $linkClass(request()->routeIs('sales.create-service')) }}">
            <x-icon name="scissors" class="h-5 w-5" />
            Registrar corte
        </a>
        @if ($isAdmin)
            <a href="{{ route('sales.create-product') }}" class="{{ $linkClass(request()->routeIs('sales.create-product')) }}">
                <x-icon name="cart" class="h-5 w-5" />
                Venta de producto
            </a>
        @endif
        <a href="{{ route('sales.index') }}" class="{{ $linkClass(request()->routeIs('sales.index', 'sales.show')) }}">
            <x-icon name="receipt" class="h-5 w-5" />
            Historial de ventas
        </a>

        @if ($isAdmin)
            <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wider text-slate-500">Administración</p>
            <a href="{{ route('services.index') }}" class="{{ $linkClass(request()->routeIs('services.*')) }}">
                <x-icon name="tag" class="h-5 w-5" />
                Tipos de corte
            </a>
            <a href="{{ route('barbers.index') }}" class="{{ $linkClass(request()->routeIs('barbers.*')) }}">
                <x-icon name="users" class="h-5 w-5" />
                Barberos
            </a>
            <a href="{{ route('users.index') }}" class="{{ $linkClass(request()->routeIs('users.*')) }}">
                <x-icon name="user" class="h-5 w-5" />
                Usuarios
            </a>
            @php($pendingPasswordRequests = \App\Models\PasswordResetRequest::pending()->count())
            <a href="{{ route('password-requests.index') }}" class="{{ $linkClass(request()->routeIs('password-requests.*')) }}">
                <x-icon name="key" class="h-5 w-5" />
                <span>Solicitudes</span>
                @if ($pendingPasswordRequests > 0)
                    <span class="ml-auto inline-flex min-w-[20px] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[11px] font-bold text-white">{{ $pendingPasswordRequests }}</span>
                @endif
            </a>
            <a href="{{ route('products.index') }}" class="{{ $linkClass(request()->routeIs('products.*')) }}">
                <x-icon name="box" class="h-5 w-5" />
                Productos
            </a>
            <a href="{{ route('closings.index') }}" class="{{ $linkClass(request()->routeIs('closings.*')) }}">
                <x-icon name="chart" class="h-5 w-5" />
                Cierres
            </a>
            <a href="{{ route('expenses.index') }}" class="{{ $linkClass(request()->routeIs('expenses.*')) }}">
                <x-icon name="minus" class="h-5 w-5" />
                Gastos
            </a>
            <a href="{{ route('settings.edit') }}" class="{{ $linkClass(request()->routeIs('settings.*')) }}">
                <x-icon name="cog" class="h-5 w-5" />
                Configuración
            </a>
        @endif
    </nav>

    <div class="border-t border-slate-800 p-3">
        <a href="{{ route('profile.edit') }}" class="{{ $linkClass(request()->routeIs('profile.*')) }}">
            <x-icon name="user" class="h-5 w-5" />
            Mi perfil
        </a>
    </div>

    <div class="border-t border-slate-800 p-4 text-xs text-slate-400">
        <p class="font-medium text-slate-200">{{ auth()->user()->name }}</p>
        <p>{{ auth()->user()->isAdmin() ? 'Administrador' : 'Barbero' }}</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="w-full rounded-lg bg-slate-800 px-3 py-2 text-center text-slate-200 hover:bg-slate-700">
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>

@php
    $user = Auth::user();

    // Menu CRUD admin: prefix nama route => label menu
    $adminMenu = [
        'admin.agency-partners' => 'Agency Partner',
        'admin.property-projects' => 'Projek Properti',
        'admin.property-files' => 'Berkas Properti',
        'admin.buyer-leads' => 'Leads Pembeli',
        'admin.digital-akads' => 'Akad Digital',
        'admin.commissions' => 'Komisi',
    ];

    // Chat hanya untuk pembeli dan agency partner
    $canChat = $user && ($user->isBuyer() || $user->isAgencyPartner());
    $unreadChats = $canChat ? \App\Models\Conversation::unreadConversationCountFor($user) : 0;

    // Bantuan (customer support): pembeli & agency chat dengan admin; admin membalas dari inbox
    $unreadSupport = $user ? \App\Models\SupportConversation::unreadConversationCountFor($user) : 0;

    // Admin: jumlah listing yang menunggu verifikasi
    $pendingListings = $user?->isAdmin() ? \App\Models\PropertyProject::where('is_verified', false)->count() : 0;
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-cream-dark">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ $user ? route('dashboard') : url('/') }}">
                        <x-brand />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 lg:-my-px lg:ms-10 lg:flex">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endauth

                    @unless ($user?->isAdmin())
                        <x-nav-link :href="route('catalog.index')" :active="request()->routeIs('catalog.*')">
                            {{ __('Katalog') }}
                        </x-nav-link>
                    @endunless

                    @if ($user?->isAgencyPartner())
                        <x-nav-link :href="route('agency.property-projects.index')" :active="request()->routeIs('agency.property-projects.*')">
                            {{ __('Listing Saya') }}
                        </x-nav-link>

                        <x-nav-link :href="route('agency.commissions.index')" :active="request()->routeIs('agency.commissions.*')">
                            {{ __('Komisi Saya') }}
                        </x-nav-link>
                    @endif

                    @if ($canChat)
                        <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')">
                            {{ __('Chat') }}
                            @if ($unreadChats)
                                <span class="ms-1.5 inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-rose-500 text-white text-[11px] font-bold">{{ $unreadChats }}</span>
                            @endif
                        </x-nav-link>

                        <x-nav-link :href="route('support.show')" :active="request()->routeIs('support.*')">
                            {{ __('Bantuan') }}
                            @if ($unreadSupport)
                                <span class="ms-1.5 inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-rose-500 text-white text-[11px] font-bold">{{ $unreadSupport }}</span>
                            @endif
                        </x-nav-link>
                    @endif

                    @if ($user?->isAdmin())
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Kelola Pengguna') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.listing-verifications.index')" :active="request()->routeIs('admin.listing-verifications.*')">
                            {{ __('Verifikasi Listing') }}
                            @if ($pendingListings)
                                <span class="ms-1.5 inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-amber-500 text-white text-[11px] font-bold">{{ $pendingListings }}</span>
                            @endif
                        </x-nav-link>

                        <x-nav-link :href="route('admin.support.index')" :active="request()->routeIs('admin.support.*')">
                            {{ __('Customer Support') }}
                            @if ($unreadSupport)
                                <span class="ms-1.5 inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-rose-500 text-white text-[11px] font-bold">{{ $unreadSupport }}</span>
                            @endif
                        </x-nav-link>

                        <div class="flex items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs(array_map(fn ($r) => $r . '.*', array_keys($adminMenu))) ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                                        <span>{{ __('Kelola Data') }}</span>
                                        <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    @foreach ($adminMenu as $routePrefix => $label)
                                        <x-dropdown-link :href="route($routePrefix . '.index')">{{ $label }}</x-dropdown-link>
                                    @endforeach
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden lg:flex lg:items-center lg:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ $user->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if ($user->isAgencyPartner())
                                <x-dropdown-link :href="route('agency.profile.edit')">
                                    {{ __('Profil Agency') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3 text-sm font-medium">
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-primary-800">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-primary-700 hover:bg-primary-800 text-white shadow-sm">{{ __('Register') }}</a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endauth

            @unless ($user?->isAdmin())
                <x-responsive-nav-link :href="route('catalog.index')" :active="request()->routeIs('catalog.*')">
                    {{ __('Katalog') }}
                </x-responsive-nav-link>
            @endunless

            @if ($user?->isAgencyPartner())
                <x-responsive-nav-link :href="route('agency.property-projects.index')" :active="request()->routeIs('agency.property-projects.*')">
                    {{ __('Listing Saya') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('agency.commissions.index')" :active="request()->routeIs('agency.commissions.*')">
                    {{ __('Komisi Saya') }}
                </x-responsive-nav-link>
            @endif

            @if ($canChat)
                <x-responsive-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')">
                    {{ __('Chat') }}{{ $unreadChats ? " ({$unreadChats})" : '' }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('support.show')" :active="request()->routeIs('support.*')">
                    {{ __('Bantuan') }}{{ $unreadSupport ? " ({$unreadSupport})" : '' }}
                </x-responsive-nav-link>
            @endif

            @if ($user?->isAdmin())
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Kelola Pengguna') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.listing-verifications.index')" :active="request()->routeIs('admin.listing-verifications.*')">
                    {{ __('Verifikasi Listing') }}{{ $pendingListings ? " ({$pendingListings})" : '' }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.support.index')" :active="request()->routeIs('admin.support.*')">
                    {{ __('Customer Support') }}{{ $unreadSupport ? " ({$unreadSupport})" : '' }}
                </x-responsive-nav-link>

                @foreach ($adminMenu as $routePrefix => $label)
                    <x-responsive-nav-link :href="route($routePrefix . '.index')" :active="request()->routeIs($routePrefix . '.*')">
                        {{ $label }}
                    </x-responsive-nav-link>
                @endforeach
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ $user->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ $user->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    @if ($user->isAgencyPartner())
                        <x-responsive-nav-link :href="route('agency.profile.edit')">
                            {{ __('Profil Agency') }}
                        </x-responsive-nav-link>
                    @endif

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('login')">{{ __('Log in') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">{{ __('Register') }}</x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>

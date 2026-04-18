@php
    $currentOutlet = outlet();
    $currentTenant = tenant();
    $moduleAccess = auth()->user()?->moduleAccess() ?? [];
    $canAccessModule = static fn (string $moduleKey): bool => (bool) ($moduleAccess[$moduleKey] ?? false);
    $canManageOutlets = $canAccessModule('outlets');
    $canAddOutlet = (auth()->user()?->isOwner() ?? false) && ($currentTenant?->canAddOutlet() ?? false);

    // Theme-based classes
    $logoGradient = match($businessType ?? 'clinic') {
        'salon' => 'from-purple-400 to-purple-500 shadow-purple-200/50',
        'barbershop' => 'from-blue-400 to-blue-500 shadow-blue-200/50',
        default => 'from-rose-400 to-rose-500 shadow-rose-200/50',
    };
    $activeClass = match($businessType ?? 'clinic') {
        'salon' => 'bg-purple-50 text-purple-600',
        'barbershop' => 'bg-blue-50 text-blue-600',
        default => 'bg-rose-50 text-rose-600',
    };
    $canViewRevenue = $canAccessModule('dashboard');
    $defaultRoute = route('subscription.expired');
    if ($canAccessModule('dashboard')) {
        $defaultRoute = route('dashboard');
    } elseif ($canAccessModule('appointments')) {
        $defaultRoute = route('appointments.index');
    } elseif ($canAccessModule('customers')) {
        $defaultRoute = route('customers.index');
    }
@endphp
<!-- Sidebar -->
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:static lg:inset-0"
>
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ $defaultRoute }}" class="flex items-center gap-2">
                @if(brand_logo())
                    <img src="{{ brand_logo() }}" alt="{{ brand_name() }}" class="h-8 w-auto">
                @else
                    <div class="w-8 h-8 bg-gradient-to-br {{ $logoGradient }} rounded-lg flex items-center justify-center shadow-lg">
                        @if($businessType === 'barbershop')
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
                            </svg>
                        @elseif($businessType === 'salon')
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        @endif
                    </div>
                @endif
                @if(!brand_logo() || brand('logo.show_text', true))
                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ brand_name() }}</span>
                @endif
            </a>
            <button @click="sidebarOpen = false" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 max-lg:block hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            @if(auth()->user()->isOwner() && $canManageOutlets)
                @php
                    $tenantOutlets = $currentTenant?->outlets()->where('status', 'active')->get() ?? collect();
                @endphp
                @if($tenantOutlets->count() > 1)
                    <!-- Outlet Switcher -->
                    <div class="mb-6 px-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2 px-2">Ganti Cabang</label>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-sm font-bold text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-gray-600 shadow-sm hover:border-rose-200 transition-all">
                                <div class="flex items-center gap-2 truncate">
                                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse flex-shrink-0"></div>
                                    <span class="truncate">{{ $currentOutlet?->name ?? '-' }}</span>
                                </div>
                                <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                @click.away="open = false" 
                                 class="absolute top-full left-0 w-full mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-[60]">
                                <div class="max-h-60 overflow-y-auto">
                                    @foreach($tenantOutlets as $o)
                                        @if($o->id === $currentOutlet?->id)
                                            <div class="flex items-center justify-between px-4 py-3 text-sm bg-rose-50 text-rose-600 dark:bg-rose-900/20 transition-colors">
                                                <span class="font-bold">{{ $o->name }}</span>
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @else
                                            <form action="{{ route('tenant.outlets.switch', $o) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full flex items-center justify-between px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                    <span class="font-medium">{{ $o->name }}</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700 p-2 bg-gray-50/50 dark:bg-gray-900/20">
                                    @if($canAddOutlet)
                                        <a href="{{ route('tenant.outlets.create') }}" class="flex items-center gap-2 px-2 py-2 text-xs font-bold text-gray-500 hover:text-rose-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            {{ __('tenant.add_outlet') }}
                                        </a>
                                    @else
                                        <a href="{{ route('tenant.billing.index') }}" class="flex items-center gap-2 px-2 py-2 text-xs font-bold text-amber-700 hover:text-amber-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ __('tenant.upgrade_plan') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mb-6 px-1">
                    @if($canAddOutlet)
                        <a href="{{ route('tenant.outlets.create') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-rose-50 dark:bg-rose-900/20 rounded-xl text-xs font-bold text-rose-600 border border-rose-100 dark:border-rose-800/50 hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            {{ __('tenant.add_outlet') }}
                        </a>
                    @else
                        <a href="{{ route('tenant.billing.index') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-xs font-bold text-amber-700 border border-amber-100 dark:border-amber-800/50 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('tenant.upgrade_plan') }}
                        </a>
                    @endif
                </div>
            @endif

            @php
                // Build nav items based on enabled features
                $navItems = [];

                if ($canAccessModule('dashboard')) {
                    $navItems[] = ['name' => __('menu.dashboard'), 'route' => 'dashboard', 'icon' => 'home', 'dividerAfter' => true];
                }

                if ($canAccessModule('appointments')) {
                    $navItems[] = ['name' => __('menu.appointments'), 'route' => 'appointments.index', 'icon' => 'calendar'];
                }
                if ($canAccessModule('customers')) {
                    $navItems[] = ['name' => __('menu.customers'), 'route' => 'customers.index', 'icon' => 'users'];
                }

                // Treatment Records - only for clinic
                if ($canAccessModule('treatment_records') && has_feature('treatment_records')) {
                    $navItems[] = ['name' => __('menu.treatment_records'), 'route' => 'treatment-records.index', 'icon' => 'document-text'];
                }

                // Service categories & services - always visible
                if ($canAccessModule('service_categories')) {
                    $navItems[] = ['name' => __('menu.service_categories'), 'route' => 'service-categories.index', 'icon' => 'collection'];
                }
                if ($canAccessModule('services')) {
                    $navItems[] = ['name' => __('menu.services'), 'route' => 'services.index', 'icon' => 'sparkles'];
                }

                // Products - conditional
                if ($canAccessModule('products') && has_feature('products')) {
                    $navItems[] = ['name' => __('menu.products'), 'route' => 'products.index', 'icon' => 'cube'];
                }

                // Packages - conditional
                if ($canAccessModule('packages') && has_feature('packages')) {
                    $navItems[] = ['name' => __('menu.packages'), 'route' => 'packages.index', 'icon' => 'gift'];
                }

                // Customer Packages - conditional
                if ($canAccessModule('customer_packages') && has_feature('customer_packages')) {
                    $navItems[] = ['name' => __('menu.customer_packages'), 'route' => 'customer-packages.index', 'icon' => 'shopping-bag'];
                }

                // Add divider before transactions
                if (count($navItems) > 0) {
                    $navItems[count($navItems) - 1]['dividerAfter'] = true;
                }

                // Transactions - always visible
                if ($canAccessModule('transactions')) {
                    $navItems[] = ['name' => __('menu.transactions'), 'route' => 'transactions.index', 'icon' => 'credit-card'];
                }

                // Loyalty - conditional
                if ($canAccessModule('loyalty') && has_feature('loyalty')) {
                    $navItems[] = ['name' => __('menu.loyalty'), 'route' => 'loyalty.index', 'icon' => 'star'];
                }

                // Reports - visible only for owner and admin with revenue access
                if ($canAccessModule('reports')) {
                    $navItems[] = ['name' => __('menu.reports'), 'route' => 'reports.index', 'icon' => 'chart-bar'];
                }

                $ownerItems = [];

                if ($canAccessModule('outlets')) {
                    $ownerItems[] = ['name' => __('menu.outlets'), 'route' => 'tenant.outlets.index', 'icon' => 'collection'];
                }

                if ($canAccessModule('import_data')) {
                    $ownerItems[] = ['name' => __('menu.import_data'), 'route' => 'imports.index', 'icon' => 'upload'];
                }
                if ($canAccessModule('staff')) {
                    $ownerItems[] = ['name' => __('menu.staff'), 'route' => 'staff.index', 'icon' => 'user-group'];
                }
                if ($canAccessModule('settings')) {
                    $ownerItems[] = ['name' => __('menu.settings'), 'route' => 'settings.index', 'icon' => 'cog'];
                }
            @endphp

            @foreach($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*') || request()->routeIs(str_replace('.index', '.*', $item['route']));
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $isActive ? $activeClass : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}"
                >
                    @include('components.icons.' . $item['icon'])
                    {{ $item['name'] }}
                </a>
                @if(isset($item['dividerAfter']) && $item['dividerAfter'])
                    <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                @endif
            @endforeach

            @if(count($ownerItems) > 0)
                <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                @foreach($ownerItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*') || request()->routeIs(str_replace('.index', '.*', $item['route']));
                    @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $isActive ? $activeClass : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}"
                    >
                        @include('components.icons.' . $item['icon'])
                        {{ $item['name'] }}
                    </a>
                @endforeach
            @endif
        </nav>

    </div>
</aside>

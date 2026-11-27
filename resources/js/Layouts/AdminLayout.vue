<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Admin', role: 'super_admin' });
const unreadInquiries = computed(() => page.props.unreadInquiriesCount || 0);

const flash = computed(() => page.props.flash || {});

const sidebarOpen = ref(true);
const mobileMenuOpen = ref(false);
const userDropdownOpen = ref(false);
const searchQuery = ref('');

// Collapsible menus
const websiteMenuOpen = ref(true);
const settingsMenuOpen = ref(false);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const logout = () => {
    router.post(route('logout'));
};

// Menu navigation items
const menuItems = [
    { name: 'Dashboard', route: 'admin.dashboard', icon: 'fa-solid fa-table-cells-large', exact: true },
    { name: 'Inquiries (Contact Form)', route: 'admin.inquiries.index', icon: 'fa-regular fa-envelope', badge: unreadInquiries },
    { name: 'Pages', route: 'admin.pages.index', icon: 'fa-regular fa-file-lines' },
];

// Header and footer menus are separate settings, so each gets its own entry.
const layoutMenuTabs = ['menu_primary', 'menu_footer'];
const websiteSubItems = [
    { name: 'Header Menu', route: 'admin.layout.index', params: { tab: 'menu_primary' }, icon: 'fa-solid fa-bars' },
    { name: 'Footer Menu', route: 'admin.layout.index', params: { tab: 'menu_footer' }, icon: 'fa-solid fa-sitemap' },
    { name: 'Site Layout & Widgets', route: 'admin.layout.index', params: { tab: 'layout_header' }, icon: 'fa-solid fa-table-columns' },
    { name: 'Shared Sections', route: 'admin.shared-sections.index', icon: 'fa-solid fa-link' },
    { name: 'Blog Articles', route: 'admin.blog.index', icon: 'fa-solid fa-newspaper' },
];

const settingsSubItems = [
    { name: 'Global Settings & SEO', route: 'admin.settings.index', icon: 'fa-solid fa-sliders' },
    { name: 'Email SMTP Setting', route: 'admin.smtp.index', icon: 'fa-solid fa-at' },
    { name: 'User Management', route: 'admin.users.index', icon: 'fa-solid fa-users-gear' },
];

const isActive = (routeName) => {
    try {
        const base = routeName.replace(/\.index$/, '');
        return route().current(routeName) || route().current(base + '.*');
    } catch (e) {
        return false;
    }
};

const currentTab = computed(() => new URL(page.url, window.location.origin).searchParams.get('tab'));

const isSubActive = (sub) => {
    if (sub.route !== 'admin.layout.index') return isActive(sub.route);
    if (!route().current('admin.layout.index')) return false;
    return layoutMenuTabs.includes(sub.params?.tab) ? currentTab.value === sub.params.tab : !layoutMenuTabs.includes(currentTab.value);
};

const filteredMenuItems = computed(() => {
    if (!searchQuery.value) return menuItems;
    const q = searchQuery.value.toLowerCase();
    return menuItems.filter(item => item.name.toLowerCase().includes(q));
});

const isDark = ref(false);
const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col antialiased text-slate-800">
        <!-- Toast Flash Notification -->
        <div v-if="flash.success" class="fixed top-5 right-5 z-50 bg-emerald-600 text-white px-5 py-3 rounded-lg shadow-lg flex items-center space-x-3 transition transform duration-300">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ flash.success }}</span>
        </div>
        <div v-if="flash.error" class="fixed top-5 right-5 z-50 bg-rose-600 text-white px-5 py-3 rounded-lg shadow-lg flex items-center space-x-3 transition transform duration-300">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <span>{{ flash.error }}</span>
        </div>

        <div class="flex flex-1 min-h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside 
                :class="[
                    sidebarOpen ? 'w-64' : 'w-20',
                    'bg-white border-r border-slate-200 flex flex-col transition-all duration-300 z-40 select-none shrink-0'
                ]"
            >
                <!-- Brand / Logo Area -->
                <div class="h-16 flex items-center px-4 border-b border-slate-200">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                            IE
                        </div>
                        <div v-if="sidebarOpen" class="flex flex-col min-w-0">
                            <span class="font-bold text-sm text-slate-800 truncate leading-tight">{{ page.props.siteConfig?.site_name || 'Innovative Eng.' }}</span>
                            <span class="text-xs text-slate-500 capitalize">{{ user.role?.replace('_', ' ') || 'Admin' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Search Menu -->
                <div v-if="sidebarOpen" class="px-3 py-2 border-b border-slate-100">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-xs text-slate-400"></i>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Search Menu Here..." 
                            class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition"
                        />
                    </div>
                </div>

                <!-- Navigation List -->
                <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-1 text-sm select-none">
                    <template v-for="item in filteredMenuItems" :key="item.name">
                        <Link 
                            :href="route(item.route)" 
                            :class="[
                                isActive(item.route) 
                                    ? 'bg-blue-600 text-white shadow-sm font-medium' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                                'flex items-center px-3 py-2.5 rounded-lg transition-colors group'
                            ]"
                            :title="!sidebarOpen ? item.name : ''"
                        >
                            <i :class="[item.icon, 'w-5 text-center text-base shrink-0', isActive(item.route) ? 'text-white' : 'text-slate-500 group-hover:text-blue-600']"></i>
                            <span v-if="sidebarOpen" class="ml-3 truncate flex-1">{{ item.name }}</span>
                            <span 
                                v-if="sidebarOpen && item.badge && item.badge.value > 0" 
                                class="ml-auto bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full font-bold"
                            >
                                {{ item.badge.value }}
                            </span>
                        </Link>
                    </template>

                    <!-- Website Management Submenu -->
                    <div class="pt-2">
                        <button 
                            type="button" 
                            @click="websiteMenuOpen = !websiteMenuOpen"
                            class="w-full flex items-center px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400 hover:text-slate-600 justify-between focus:outline-none"
                        >
                            <span v-if="sidebarOpen" class="flex items-center space-x-2">
                                <i class="fa-solid fa-globe text-xs"></i>
                                <span>Website Management</span>
                            </span>
                            <i v-if="sidebarOpen" :class="['fa-solid text-xs transition-transform', websiteMenuOpen ? 'fa-chevron-down' : 'fa-chevron-right']"></i>
                        </button>
                        <div v-show="websiteMenuOpen && sidebarOpen" class="mt-1 space-y-1 pl-2 border-l-2 border-slate-100 ml-3">
                            <Link 
                                v-for="sub in websiteSubItems" 
                                :key="sub.name" 
                                :href="route(sub.route, sub.params || {})"
                                :class="[
                                    isSubActive(sub)
                                        ? 'text-blue-600 font-semibold bg-blue-50' 
                                        : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50',
                                    'flex items-center px-3 py-2 rounded-lg text-xs transition'
                                ]"
                            >
                                <i :class="[sub.icon, 'w-4 text-center mr-2 text-slate-400']"></i>
                                <span>{{ sub.name }}</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Global Settings Submenu -->
                    <div class="pt-2">
                        <button 
                            type="button" 
                            @click="settingsMenuOpen = !settingsMenuOpen"
                            class="w-full flex items-center px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400 hover:text-slate-600 justify-between focus:outline-none"
                        >
                            <span v-if="sidebarOpen" class="flex items-center space-x-2">
                                <i class="fa-solid fa-gear text-xs"></i>
                                <span>Global Settings</span>
                            </span>
                            <i v-if="sidebarOpen" :class="['fa-solid text-xs transition-transform', settingsMenuOpen ? 'fa-chevron-down' : 'fa-chevron-right']"></i>
                        </button>
                        <div v-show="settingsMenuOpen && sidebarOpen" class="mt-1 space-y-1 pl-2 border-l-2 border-slate-100 ml-3">
                            <Link 
                                v-for="sub in settingsSubItems" 
                                :key="sub.name" 
                                :href="route(sub.route)"
                                :class="[
                                    isActive(sub.route) 
                                        ? 'text-blue-600 font-semibold bg-blue-50' 
                                        : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50',
                                    'flex items-center px-3 py-2 rounded-lg text-xs transition'
                                ]"
                            >
                                <i :class="[sub.icon, 'w-4 text-center mr-2 text-slate-400']"></i>
                                <span>{{ sub.name }}</span>
                            </Link>
                        </div>
                    </div>

                    <!-- System Cache / Backup -->
                    <div class="pt-2">
                        <Link 
                            :href="route('admin.system.cache-clear')" 
                            method="post" 
                            as="button"
                            class="w-full flex items-center px-3 py-2 text-xs text-slate-500 hover:bg-slate-100 hover:text-slate-700 rounded-lg transition"
                        >
                            <i class="fa-solid fa-arrows-rotate w-5 text-center text-slate-400"></i>
                            <span v-if="sidebarOpen" class="ml-3">Clear Cache</span>
                        </Link>
                    </div>
                </nav>

                <!-- Footer version -->
                <div v-if="sidebarOpen" class="p-3 border-t border-slate-100 text-center text-[11px] text-slate-400">
                    Innovative Engineering Management
                </div>
            </aside>

            <!-- Main Workspace Container -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Header Bar -->
                <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
                    <div class="flex items-center space-x-3">
                        <button 
                            type="button" 
                            @click="toggleSidebar"
                            class="hidden lg:flex p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg focus:outline-none"
                            title="Toggle Sidebar"
                        >
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                        <button 
                            type="button" 
                            @click="toggleMobileMenu"
                            class="lg:hidden p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg focus:outline-none"
                        >
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                        <h1 class="text-base font-semibold text-slate-700 hidden sm:block">{{ page.props.siteConfig?.site_name || 'Innovative Engineering' }} Management</h1>
                    </div>

                    <!-- Right Controls -->
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <!-- Go to Home -->
                        <a 
                            href="/" 
                            target="_blank" 
                            class="flex items-center space-x-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition"
                            title="Open public website in new tab"
                        >
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            <span>Go to Home</span>
                        </a>

                        <!-- Notifications / Inquiries -->
                        <Link 
                            :href="route('admin.inquiries.index')" 
                            class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                            title="Customer Inquiries"
                        >
                            <i class="fa-regular fa-bell text-lg"></i>
                            <span 
                                v-if="unreadInquiries > 0" 
                                class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white"
                            ></span>
                        </Link>

                        <!-- Settings Icon -->
                        <Link 
                            :href="route('admin.settings.index')" 
                            class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                            title="Settings"
                        >
                            <i class="fa-solid fa-gear text-lg"></i>
                        </Link>

                        <!-- Dark/Light Mode -->
                        <button 
                            type="button" 
                            @click="toggleDarkMode"
                            class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                            title="Toggle Mode"
                        >
                            <i :class="[isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon', 'text-lg']"></i>
                        </button>

                        <!-- User Profile Dropdown -->
                        <div class="relative">
                            <button 
                                type="button" 
                                @click="userDropdownOpen = !userDropdownOpen"
                                class="flex items-center space-x-2 focus:outline-none"
                            >
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                    {{ user.name ? user.name.slice(0, 2) : 'AD' }}
                                </div>
                                <span class="hidden md:inline-block text-xs font-medium text-slate-700">{{ user.name }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>

                            <div 
                                v-show="userDropdownOpen" 
                                @click="userDropdownOpen = false"
                                class="fixed inset-0 z-40"
                            ></div>

                            <div 
                                v-show="userDropdownOpen" 
                                class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-50 text-xs"
                            >
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <div class="font-semibold text-slate-800">{{ user.name }}</div>
                                    <div class="text-slate-400 truncate">{{ user.email }}</div>
                                </div>
                                <Link :href="route('profile.show')" class="block px-4 py-2 text-slate-600 hover:bg-slate-50">Profile Settings</Link>
                                <Link :href="route('admin.users.index')" class="block px-4 py-2 text-slate-600 hover:bg-slate-50">Staff Accounts</Link>
                                <button 
                                    type="button" 
                                    @click="logout" 
                                    class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 border-t border-slate-100"
                                >
                                    Sign Out
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content Area -->
                <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

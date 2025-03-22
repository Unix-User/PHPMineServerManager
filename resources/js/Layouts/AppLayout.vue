<style>
/* Hide scrollbar for all browsers */
::-webkit-scrollbar {
    display: none;
}

/* For Firefox */
* {
    scrollbar-width: none;
}

/* For IE and Edge */
* {
    -ms-overflow-style: none;
}

/* Smooth transitions for theme changes */
.theme-transition {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, opacity 0.3s ease;
}

/* Ensure dropdowns appear above other content */
.dropdown-content {
    z-index: 1000;
}

/* Ensure responsive menu appears above content */
.responsive-menu {
    z-index: 999;
    position: relative;
}
</style>

<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import FloatingChat from "@/Components/FloatingChat.vue";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(
        route("current-team.update"),
        { team_id: team.id },
        { preserveState: false }
    );
};

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div :class="[$page.props.user.preferredTheme === 'dark' ? 'dark' : '']" class="theme-transition">
        <Head :title="title" />
        <Banner />
        <div style="background-image: url('/storage/background.png'); background-size: cover; background-attachment: fixed; background-position: center;"
            class="min-h-screen bg-cover bg-center bg-no-repeat bg-fixed dark:bg-blend-multiply dark:bg-opacity-90">
            <nav class="bg-white/90 backdrop-blur-sm dark:bg-gray-800/90 shadow-md relative z-50">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link href="/" class="transition-transform hover:scale-105">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('home')" :active="route().current('home')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:text-blue-500 dark:hover:text-blue-300" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('home')}">Home</NavLink>
                                <NavLink v-if="$page.props.user.roles.includes('admin')" :href="route('backups')" :active="route().current('backups')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:text-blue-500 dark:hover:text-blue-300" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('backups')}">Backups</NavLink>
                                <NavLink v-if="$page.props.user.roles.includes('admin')" :href="route('rcon')" :active="route().current('rcon')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:text-blue-500 dark:hover:text-blue-300" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('rcon')}">Rcon</NavLink>
                                <NavLink :href="route('shop')" :active="route().current('shop')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:text-blue-500 dark:hover:text-blue-300" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('shop')}">Loja</NavLink>
                                <NavLink :href="route('map')" :active="route().current('map')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:text-blue-500 dark:hover:text-blue-300" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('map')}">Mapa</NavLink>
                            </div>
                        </div>
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60" class="text-gray-700 bg-gray-100/90 hover:bg-gray-200/90 rounded-lg shadow-lg backdrop-blur-sm dark:text-gray-200 dark:bg-gray-700/90 dark:hover:bg-gray-600/90 transition-all dropdown-content">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-opacity-50 transition-all">
                                                {{ $page.props.auth.user.current_team.name }}
                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div class="w-60 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-lg shadow-lg">
                                            <!-- Team Management -->
                                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                                <div class="block px-4 py-2 text-xs text-gray-500 dark:text-gray-300 font-semibold">Gerenciar Equipe</div>
                                                <!-- Team Settings -->
                                                <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)" class="transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">Configurações da Equipe</DropdownLink>
                                                <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" class="transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">Criar Nova Equipe</DropdownLink>
                                                <!-- Team Switcher -->
                                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                    <div class="border-t border-gray-200 dark:border-gray-600" />
                                                    <div class="block px-4 py-2 text-xs text-gray-500 dark:text-gray-300 font-semibold">Trocar de Equipe</div>
                                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                        <form @submit.prevent="switchToTeam(team)">
                                                            <DropdownLink as="button" class="w-full text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                                                                <div class="flex items-center">
                                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="mr-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    <div>{{ team.name }}</div>
                                                                </div>
                                                            </DropdownLink>
                                                        </form>
                                                    </template>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48" class="text-gray-700 bg-gray-100/90 hover:bg-gray-200/90 rounded-full shadow-lg backdrop-blur-sm dark:text-gray-200 dark:bg-gray-700/90 dark:hover:bg-gray-600/90 transition-all">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-opacity-50 transition">
                                            <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                                        </button>
                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-opacity-50 transition-all">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-lg shadow-lg">
                                            <!-- Account Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-500 dark:text-gray-300 font-semibold">Gerenciar Conta</div>
                                            <DropdownLink :href="route('profile.show')" class="transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">Perfil</DropdownLink>
                                            <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" class="transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">Tokens de API</DropdownLink>
                                            <div class="border-t border-gray-200 dark:border-gray-600" />
                                            <!-- Authentication -->
                                            <form @submit.prevent="logout">
                                                <DropdownLink as="button" class="transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-red-600 dark:hover:text-red-400">Sair</DropdownLink>
                                            </form>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100/80 dark:hover:bg-gray-700/80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-opacity-50 transition-all" @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm">
                        <ResponsiveNavLink :href="route('home')" :active="route().current('home')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('home')}">Home</ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.user.roles.includes('admin')" :href="route('backups')" :active="route().current('backups')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('backups')}">Backups</ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.user.roles.includes('admin')" :href="route('rcon')" :active="route().current('rcon')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('rcon')}">Rcon</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('shop')" :active="route().current('shop')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('shop')}">Loja</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('map')" :active="route().current('map')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('map')}">Mapa</ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 mr-3">
                                <img class="h-10 w-10 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-600" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ $page.props.auth.user.name }}</div>
                                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('profile.show')}">Perfil</ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('api-tokens.index')}">Tokens de API</ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80 hover:text-red-600 dark:hover:text-red-400">Sair</ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200 dark:border-gray-600" />

                                <div class="block px-4 py-2 text-xs text-gray-500 dark:text-gray-300 font-semibold">Gerenciar Equipe</div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('teams.show')}">Configurações da Equipe</ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80" v-bind:class="{'text-blue-600 dark:text-blue-400': route().current('teams.create')}">Criar Nova Equipe</ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200 dark:border-gray-600" />

                                    <div class="block px-4 py-2 text-xs text-gray-500 dark:text-gray-300 font-semibold">Trocar de Equipe</div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button" class="font-semibold text-gray-800 dark:text-gray-200 transition-colors hover:bg-gray-100/80 dark:hover:bg-gray-700/80">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="mr-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm text-gray-900 dark:text-white shadow-md">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="pb-16">
                <slot />
                <div class="fixed bottom-4 right-4 z-50">
                    <FloatingChat class="shadow-lg transition-transform hover:scale-105" />
                </div>
            </main>
        </div>
    </div>
</template>
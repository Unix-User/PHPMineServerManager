<style>

::-webkit-scrollbar {
    display: none;
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
    <div>
        <Head :title="title" />
        <Banner />
        <div style="background-image: url('/storage/background.png'); background-size: cover; background-attachment: fixed; background-position: center;"
            class="min-h-screen bg-cover bg-center bg-no-repeat bg-fixed">
            <nav class="bg-white dark:bg-gray-800">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link href="/">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('dashboard')}">Painel de Controle</NavLink>
                                <NavLink :href="route('backups')" :active="route().current('backups')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('backups')}">Gerenciamento de Backups</NavLink>
                                <NavLink v-if="$page.props.user.roles.includes('admin')" :href="route('rcon')" :active="route().current('rcon')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('rcon')}">Controle Rcon</NavLink>
                                <NavLink :href="route('assinatura.vip')" :active="route().current('assinatura.vip')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('assinatura.vip')}">Assinatura VIP</NavLink>
                                <NavLink :href="route('map')" :active="route().current('map')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('map')}">Mapa Interativo</NavLink>
                                <NavLink href="help" :active="route().current('help')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('help')}">Tutoriais e Guias</NavLink>
                                <NavLink :href="route('updates')" :active="route().current('updates')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('updates')}">Atualizações do Sistema</NavLink>
                            </div>
                        </div>
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60" :class="{'text-gray-200 bg-gray-700 hover:bg-gray-600 rounded-lg shadow': $page.props.user.preferredTheme === 'dark', 'dark:text-gray-300 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg shadow dark:bg-gray-900': $page.props.user.preferredTheme !== 'dark'}">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md hover:text-gray-900 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.current_team.name }}
                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                                <div class="block px-4 py-2 text-xs text-gray-400">Gerenciar Equipe</div>
                                                <!-- Team Settings -->
                                                <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">Configurações da Equipe</DropdownLink>
                                                <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">Criar Nova Equipe</DropdownLink>
                                                <!-- Team Switcher -->
                                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                    <div class="border-t border-gray-200" />
                                                    <div class="block px-4 py-2 text-xs text-gray-400">Trocar de Equipe</div>
                                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                        <form @submit.prevent="switchToTeam(team)">
                                                            <DropdownLink as="button">
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
                                <Dropdown align="right" width="48" :class="{'text-gray-200 bg-gray-700 hover:bg-gray-600 rounded-full shadow': $page.props.user.preferredTheme === 'dark', 'text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-full shadow': $page.props.user.preferredTheme !== 'dark'}">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                                        </button>
                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">Gerenciar Conta</div>
                                        <DropdownLink :href="route('profile.show')">Perfil</DropdownLink>
                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">Tokens de API</DropdownLink>
                                        <div class="border-t border-gray-200" />
                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">Sair</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" @click="showingNavigationDropdown = !showingNavigationDropdown">
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
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('dashboard')}">Painel de Controle</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('backups')" :active="route().current('backups')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('backups')}">Gerenciamento de Backups</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('rcon')" :active="route().current('rcon')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('rcon')}">Controle Rcon</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('assinatura.vip')" :active="route().current('assinatura.vip')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('assinatura.vip')}">Assinatura VIP</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('map')" :active="route().current('map')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('map')}">Mapa Interativo</ResponsiveNavLink>
                        <ResponsiveNavLink href="help" :active="route().current('help')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('help')}">Tutoriais e Guias</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('updates')" :active="route().current('updates')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('updates')}">Atualizações do Sistema</ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 mr-3">
                                <img class="h-10 w-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">{{ $page.props.auth.user.name }}</div>
                                <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('profile.show')}">Perfil</ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('api-tokens.index')}">Tokens de API</ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button" class="font-semibold">Sair</ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200" />

                                <div class="block px-4 py-2 text-xs text-gray-400">Gerenciar Equipe</div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('teams.show')}">Configurações da Equipe</ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')" class="font-semibold" v-bind:class="{'text-blue-600': route().current('teams.create')}">Criar Nova Equipe</ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200" />

                                    <div class="block px-4 py-2 text-xs text-gray-400">Trocar de Equipe</div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button" class="font-semibold">
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
            <header v-if="$slots.header" :class="{'dark:bg-gray-800/50 dark:text-white': $page.props.user.preferredTheme !== 'dark', 'bg-white/50 text-gray-900': $page.props.user.preferredTheme === 'dark'}">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
                <div class="ml-3 md:3">
                    <FloatingChat />
                </div>
            </main>
        </div>
    </div>
</template>


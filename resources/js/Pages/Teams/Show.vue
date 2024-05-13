<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteTeamForm from "@/Pages/Teams/Partials/DeleteTeamForm.vue";
import SectionBorder from "@/Components/SectionBorder.vue";
import TeamMemberManager from "@/Pages/Teams/Partials/TeamMemberManager.vue";
import UpdateTeamNameForm from "@/Pages/Teams/Partials/UpdateTeamNameForm.vue";

defineProps({
    team: Object,
    availableRoles: Array,
    permissions: Object,
});
</script>

<template>
    <AppLayout title="Configurações do Time">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Gerenciamento do Time
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div v-if="$page.props.user.roles.includes('admin')">
                    <UpdateTeamNameForm
                        :team="team"
                        :permissions="permissions"
                    />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.user.roles.includes('admin')">
                    <TeamMemberManager
                        :team="team"
                        :available-roles="availableRoles"
                        :user-permissions="permissions"
                    />

                    <SectionBorder />
                </div>

                <template
                    v-if="permissions.canDeleteTeam && !team.personal_team"
                >
                    <DeleteTeamForm :team="team" />

                    <SectionBorder />
                </template>
            </div>
        </div>
    </AppLayout>
</template>

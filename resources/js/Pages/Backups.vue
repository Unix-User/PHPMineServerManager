<script>
import { ref, defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    type: {
        type: String,
        default: "submit",
    },
});

const isLoading = ref(false);

const fetchBusyStatus = async () => {
    try {
        const response = await axios.get("/busy");
        isLoading.value = response.data.isBusy === true;
    } catch (error) {
        console.error(`AxiosError: ${error.message}`);
    }
};

export default {
    components: {
        AppLayout,
        PrimaryButton,
    },
    setup() {
        const iframeSrc = ref(
            "https://drive.google.com/embeddedfolderview?id=1ce6Uen0tXTAwM_URk2KfKv6LwpbF77Nk#grid"
        );
        fetchBusyStatus();
        return {
            isLoading,
            iframeSrc,
        };
    },
};
</script>
<template>
    <AppLayout title="Backups">
        <template
            #header
            class="flex flex-col sm:flex-row justify-between items-center"
        >
            <div class="flex flex-col sm:flex-row justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Backups
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div class="container mx-auto" id="content">
                        <div id="mapRow">
                            <div
                                class="bg-white dark:bg-gray-800 rounded shadow-sm p-4 m-4"
                                id="mapContent"
                            >
                                <div class="flex justify-between items-center">
                                    <h2
                                        class="text-xl font-semibold text-gray-900 dark:text-white"
                                    >
                                        Google Drive Backups
                                    </h2>
                                </div>
                                <iframe
                                    :src="iframeSrc"
                                    style="width: 100%; height: 500px"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
@media (max-width: 640px) {
    #mapContent {
        padding: 0;
    }
}
</style>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cover bg-center bg-no-repeat bg-fixed"
        :style="`background-image: url('storage/background.png');`">
        <div>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 shadow-md overflow-hidden sm:rounded-lg"
            :class="{'bg-white': $root.theme === 'light', 'bg-gray-800 text-white': $root.theme === 'dark'}"
            :style="{'background-color': $root.theme === 'light' ? '#f8fafc' : '#1a202c'}">
            <slot />
        </div>
    </div>
</template>

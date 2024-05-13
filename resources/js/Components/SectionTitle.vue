<template>
    <div class="md:col-span-1 flex justify-between dark:bg-gray-800 bg-white">
        <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium dark:text-white text-gray-900">
                <slot name="title" />
            </h3>

            <p class="mt-1 text-sm dark:text-gray-300 text-gray-600">
                <slot name="description" />
            </p>
        </div>

        <div class="px-4 sm:px-0">
            <slot name="aside" />
        </div>
    </div>
</template>

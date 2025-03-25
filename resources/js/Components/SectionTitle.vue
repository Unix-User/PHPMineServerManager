<template>
    <div class="md:col-span-1 flex justify-between items-start dark:bg-gray-800 bg-white rounded-lg p-6">
        <div class="flex flex-col space-y-4">
            <h3 class="text-lg font-medium leading-tight dark:text-white text-gray-900">
                <slot name="title" />
            </h3>

            <p class="text-sm dark:text-gray-300 text-gray-600">
                <slot name="description" />
            </p>
        </div>

        <div>
            <slot name="aside" />
        </div>
    </div>
</template>

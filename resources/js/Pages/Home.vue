<script setup>
import { ref, computed } from "vue";
import { Head, useForm, Link } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import Calendar from '../components/Calendar.vue';

defineProps({
    LEAB_petCabins: {
        type: Array,
        default: () => [],
    },
    ABLE_petCabins: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Northlink Trip Availability" />

    <div
        class="
            items-top
            justify-center
            min-h-screen
            bg-gray-100
            dark:bg-gray-900
            sm:items-center sm:pt-0
        "
        style="padding-bottom: 25px"
    >
        <NavBar />

        <Calendar
            :start-date="new Date(2022, 9, 1)"
            :data="LEAB_petCabins"
        />

        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
                <h1 class="text-4xl text-gray-600 dark:text-gray-200">
                    Northlink Trip Availability
                </h1>
            </div>
            <div
                class="
                    mt-8
                    bg-white
                    dark:bg-gray-800
                    overflow-hidden
                    shadow
                    sm:rounded-lg
                "
            >
                <div class="grid grid-cols-1 md:grid-cols-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div
                                class="
                                    ml-4
                                    text-lg text-gray-600
                                    leading-7
                                    font-semibold
                                "
                            >
                                <h1
                                    class="
                                        text-2xl text-gray-600
                                        dark:text-gray-200
                                    "
                                >
                                    Lerwick to Aberdeen
                                </h1>
                                <div
                                    class="
                                        mt-2
                                        text-gray-500
                                        dark:text-gray-400
                                        text-sm
                                    "
                                >
                                    <ul>
                                        <li
                                            v-for="petCabin in LEAB_petCabins"
                                            :key="petCabin.id"
                                        >
                                            <span
                                                class="
                                                    text-gray-600
                                                    dark:text-gray-200
                                                    mr-4
                                                "
                                                >{{ petCabin.date }}</span
                                            >
                                            <span
                                                class="
                                                    text-gray-600
                                                    dark:text-gray-200
                                                "
                                                v-if="petCabin.available"
                                            >
                                                ✅
                                            </span>
                                            <span
                                                class="
                                                    text-gray-600
                                                    dark:text-gray-200
                                                "
                                                v-else
                                            >
                                                ⛔️
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div
                                class="
                                    ml-4
                                    text-lg text-gray-600
                                    leading-7
                                    font-semibold
                                "
                            >
                                <h1
                                    class="
                                        text-2xl text-gray-600
                                        dark:text-gray-200
                                    "
                                >
                                    Aberdeen to Lerwick
                                </h1>
                                <div
                                    class="
                                        mt-2
                                        text-gray-500
                                        dark:text-gray-400
                                        text-sm
                                    "
                                >
                                    <ul>
                                        <li
                                            v-for="petCabin in ABLE_petCabins"
                                            :key="petCabin.id"
                                        >
                                            <span
                                                class="
                                                    text-gray-600
                                                    dark:text-gray-200
                                                    mr-4
                                                "
                                                >{{ petCabin.date }}</span
                                            >
                                            <span
                                                class="
                                                    text-gray-600
                                                    dark:text-gray-200
                                                "
                                                v-if="petCabin.available"
                                            >
                                                ✅
                                            </span>
                                            <span
                                                class="
                                                    text-gray-600
                                                    dark:text-gray-200
                                                "
                                                v-else
                                            >
                                                ⛔️
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
progress::-webkit-progress-bar {
    background-color: white;
}

progress::-webkit-progress-value {
    transition: width 0.1s;
}

.bg-gray-100 {
    background-color: #f7fafc;
    background-color: rgba(247, 250, 252, var(--tw-bg-opacity));
}

.text-gray-500 {
    color: #a0aec0;
    color: rgba(160, 174, 192, var(--tw-text-opacity));
}

.text-gray-600 {
    color: #718096;
    color: rgba(113, 128, 150, var(--tw-text-opacity));
}

.text-gray-700 {
    color: #4a5568;
    color: rgba(74, 85, 104, var(--tw-text-opacity));
}

.text-gray-900 {
    color: #1a202c;
    color: rgba(26, 32, 44, var(--tw-text-opacity));
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-gray-800 {
        background-color: #2d3748;
        background-color: rgba(45, 55, 72, var(--tw-bg-opacity));
    }

    .dark\:bg-gray-900 {
        background-color: #1a202c;
        background-color: rgba(26, 32, 44, var(--tw-bg-opacity));
    }

    .dark\:border-gray-700 {
        border-color: #4a5568;
        border-color: rgba(74, 85, 104, var(--tw-border-opacity));
    }

    .dark\:text-white {
        color: #fff;
        color: rgba(255, 255, 255, var(--tw-text-opacity));
    }
}
</style>

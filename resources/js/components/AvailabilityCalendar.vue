<script setup>
import "v-calendar/dist/style.css";
import axios from "axios";
import { ref } from "@vue/runtime-core";

const props = defineProps({
    requestData: {
        type: Function,
        required: true,
    },
    dates: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
});

const today = new Date();
const month = today.getMonth() + 1;
const year = today.getFullYear();

props.requestData(month, year);

const getAvailabilityClass = (date, route) => {
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

    const formattedDate = `${year}-${month}-${day}`;

    const foundDate = props.dates[route].find(
        (item) => item.date === formattedDate
    );

    if (!foundDate) {
        return "bg-gray-200";
    }

    if (foundDate.past) return "bg-indigo-300";

    return foundDate.available ? "bg-green-500" : "bg-red-500";
};

const updateFromPage = ({ month, year }, route) => {
    props.requestData(month, year, route);
};
</script>

<template>
    <div>
        <div class="flex justify-center pt-8 sm:justify-start sm:pt-0 mb-4">
            <h1 class="text-4xl text-gray-600 dark:text-gray-200">
                {{ title }}
            </h1>
        </div>

        <p class="mb-2">
            {{ description }}
        </p>
        <div class="mb-2">
            <div class="flex items-center">
                <div class="availability-dot bg-green-500" />
                <span class="ml-3">Available</span>
            </div>
            <div class="flex items-center">
                <div class="availability-dot bg-red-500" />
                <span class="ml-3">Unavailable</span>
            </div>
            <div class="flex items-center">
                <div class="availability-dot bg-indigo-300" />
                <span class="ml-3">In the past</span>
            </div>
            <div class="flex items-center">
                <div class="availability-dot bg-gray-200" />
                <span class="ml-3">Unknown</span>
            </div>
        </div>

        <hr class="my-4" />

        <div v-for="(route, index) in ['LEAB', 'ABLE']" :key="route">
            <div class="flex justify-center">
                <p
                    v-if="route === 'LEAB'"
                    class="text-2xl text-gray-600 dark:text-gray-200"
                >
                    Lerwick to Aberdeen
                </p>
                <p
                    v-if="route === 'ABLE'"
                    class="text-2xl text-gray-600 dark:text-gray-200"
                >
                    Aberdeen to Lerwick
                </p>
            </div>

            <Calendar
                class="mb-6"
                is-expanded
                @update:from-page="(value) => updateFromPage(value, route)"
            >
                <template v-slot:day-content="{ day, dayEvents }">
                    <div v-on="dayEvents">
                        <div class="flex justify-center">{{ day.label }}</div>
                        <div class="flex justify-center mb-4">
                            <div
                                class="availability-dot"
                                :class="getAvailabilityClass(day.date, route)"
                            />
                        </div>
                    </div>
                </template>
            </Calendar>

            <hr v-if="index === 0" class="mb-4" />
        </div>
    </div>
</template>

<style scoped>
.availability-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin: 0 5px;
}

.available {
    background-color: green;
}

.unavailable {
    background-color: red;
}

.unknown {
    background-color: gray;
}
</style>
<script setup>
import "v-calendar/dist/style.css";
import axios from "axios";
import { ref } from "@vue/runtime-core";

const dates = ref({
    LEAB: [],
    ABLE: [],
});

const getPetCabinData = async (month, year, route = null) => {
    const { data } = await axios.get(`/api/capacity/${month}/${year}/${route}`);

    dates.value[route] = data;
};

const today = new Date();
const month = today.getMonth() + 1;
const year = today.getFullYear();

getPetCabinData(month, year, 'LEAB');
getPetCabinData(month, year, 'ABLE');

const getCapacity = (date, route) => {
    const year = date.getFullYear()
    const month = date.getMonth() + 1;
    const day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

    const formattedDate = `${year}-${month}-${day}`

    const foundDate = dates.value[route].find((item) => item.date === formattedDate);

    if (!foundDate) {
        return -1;
    }

    return Number(foundDate.capacity);
};

const updateFromPage = ({ month, year }, route) => {
    getPetCabinData(month, year, route);
};
</script>

<template>
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
                                class="rounded-full px-2 py-1 text-xs font-bold text-center"
                                :class="{
                                    'bg-green-500': getCapacity(day.date, route) > 400,
                                    'bg-orange-500': getCapacity(day.date, route) > 200 && getCapacity(day.date, route) < 400,
                                    'bg-red-500': getCapacity(day.date, route) < 200 && getCapacity(day.date, route) > 0,
                                    'bg-gray-200': getCapacity(day.date, route) === -1,
                                }"
                            >
                                <span v-if="getCapacity(day.date, route) >= 0">
                                    {{ getCapacity(day.date, route) }}
                                </span>
                                <span v-else>-</span>
                            </div>
                    </div>
                </div>
            </template>
        </Calendar>

        <hr v-if="index === 0" class="mb-4" />
    </div>
</template>

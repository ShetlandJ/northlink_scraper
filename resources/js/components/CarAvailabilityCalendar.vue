<script setup>
import "v-calendar/dist/style.css";
import axios from "axios";
import { ref } from "@vue/runtime-core";

const dates = ref({
    LEAB: [],
    ABLE: [],
});

const getPetCabinData = async (month, year, route = null) => {
    const { data } = await axios.get(`/api/pet-cabins/${month}/${year}`);

    if (!route) {
        dates.value.LEAB = data.LEAB;
        dates.value.ABLE = data.ABLE;
    } else {
        dates.value[route] = data[route];
    }
};

const today = new Date();
const month = today.getMonth() + 1;
const year = today.getFullYear();

getPetCabinData(month, year);

const getAvailabilityClass = (date, route) => {
    const year = date.getFullYear()
    const month = date.getMonth() + 1;
    const day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

    const monthString = month.toString().length === 1 ? "0" + month : month;
    const formattedDate = `${year}-${monthString}-${day}`;

    const foundDate = dates.value[route].find((item) => item.date === formattedDate);

    if (!foundDate) {
        return "bg-gray-200";
    }

    return foundDate.available ? "bg-green-500" : "bg-red-500";
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
                            class="availability-dot"
                            :class="getAvailabilityClass(day.date, route)"
                        />
                    </div>
                </div>
            </template>
        </Calendar>

        <hr v-if="index === 0" class="mb-4" />
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
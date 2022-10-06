<script setup>
import "v-calendar/dist/style.css";
import axios from "axios";
import { ref } from "@vue/runtime-core";

// const props = defineProps({
//     data: {
//         type: Array,
//         default: () => [],
//     },
// })

const dates = ref({
    LEAB: [],
    ABLE: [],
});

const getPetCabinData = async (firstSearch = true) => {
    const { data } = await axios.get("/api/pet-cabins/10/2022");

    dates.value.LEAB = data.LEAB;
    dates.value.ABLE = data.ABLE;

    console.log(dates.value);
};

getPetCabinData();

const getAvailabilityClass = (date, route) => {
    const formattedDate =
        date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();

    const foundDate = dates.value[route].find(
        (item) => item.date === formattedDate
    );

    if (!foundDate) {
        return "bg-gray-200";
    }

    return foundDate.available ? "bg-green-500" : "bg-red-500";
};
</script>

<template>
    <div v-for="route in ['LEAB', 'ABLE']" :key="route">
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

        <Calendar is-expanded>
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
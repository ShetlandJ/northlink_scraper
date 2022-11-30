<script setup>
import "v-calendar/dist/style.css";
import axios from "axios";
import { computed, ref, watch } from "@vue/runtime-core";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    apiRoute: {
        type: String,
        required: true,
    },
    routePayload: {
        type: Object,
        default: null,
    },
});

const dates = ref({
    LEAB: [],
    ABLE: [],
});

const loading = ref(false);

const viewingMonth = ref(null);
const viewingYear = ref(null);

const requestData = async (month, year, route = null) => {
    loading.value = true;
    let params = "";
    if (props.routePayload) {
        params = new URLSearchParams(props.routePayload).toString();
    }

    viewingMonth.value = month;
    viewingYear.value = year;

    const { data } = await axios.get(
        `/api/${props.apiRoute}/${month}/${year}?${params}`
    );

    if (!route) {
        dates.value.LEAB = data.LEAB;
        dates.value.ABLE = data.ABLE;
    } else {
        dates.value[route] = data[route];
    }
    loading.value = false;
};

const today = new Date();
const month = today.getMonth() + 1;
const year = today.getFullYear();

requestData(month, year);

const getAvailabilityClass = (date, route) => {
    const foundDate = findDate(date, route);

    if (!foundDate) {
        return "bg-gray-200";
    }

    if (foundDate.past) return "bg-indigo-300";

    return foundDate.available ? "bg-green-500" : "bg-red-500";
};

const getRemaining = (date, route) => {
    const foundDate = findDate(date, route);

    if (!foundDate) {
        return 0;
    }

    return foundDate.capacity;
};

const getPrice = (date, route) => {
    const foundDate = findDate(date, route);

    if (!foundDate) {
        return "";
    }

    return foundDate.price;
};

const isAvailable = (date, route) => {
    const foundDate = findDate(date, route);

    if (!foundDate) {
        return false;
    }

    return foundDate.available;
};

const inPast = (date, route) => {
    const foundDate = findDate(date, route);

    if (!foundDate) {
        return false;
    }

    return foundDate.past;
};

const pricesListConfig = computed(() => {
    const prices = {
        ABLE: [],
        LEAB: [],
    };

    dates.value.ABLE.filter((item) => item.available).forEach((item) => {
        prices.ABLE.push(item.price);
    });
    // remove duplicates
    prices.ABLE = [...new Set(prices.ABLE)];

    dates.value.LEAB.filter((item) => item.available).forEach((item) => {
        prices.LEAB.push(item.price);
    });
    // remove duplicates
    prices.LEAB = [...new Set(prices.LEAB)];

    return prices;
});

const updateFromPage = ({ month, year }, route) => {
    requestData(month, year, route);
};

const findDate = (date, route) => {
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

    const monthString = month.toString().length === 1 ? "0" + month : month;
    const formattedDate = `${year}-${monthString}-${day}`;

    if (!dates.value[route]) {
        return null;
    }

    const foundDate = dates.value[route].find(
        (item) => item.date === formattedDate
    );

    return foundDate;
}

const getPriceClass = (date, route) => {
    const foundDate = findDate(date, route);

    const prices = pricesListConfig.value[route];
    const price = foundDate.price;
    prices.sort((a, b) => a - b);
    const pricesLength = prices.length;
    const priceIndex = prices.indexOf(price);

    if (priceIndex === 0) {
        return "green text-white";
    } else if (priceIndex === pricesLength - 1) {
        return "red text-white";
    } else {
        return "yellow";
    }
};

const getRemainingClass = (date, route) => {
    const foundDate = findDate(date, route);

    const remaining = foundDate.capacity;

    if (remaining < 3) {
        return "red text-white";
    } else if (remaining <= 7) {
        return "orange";
    } else if (remaining <= 10) {
        return "yellow";
    }

    return 'blue text-white';
};

watch(
    () => props.routePayload,
    () => requestData(viewingMonth.value, viewingYear.value)
);
</script>

<template>
    <div>
        <div class="flex justify-center sm:pt-8 sm:justify-start sm:pt-0 mb-4">
            <h1 class="text-4xl text-gray-600 dark:text-white">
                {{ title }}
            </h1>
        </div>

        <p class="mb-2 dark:text-white">
            {{ description }}
        </p>

        <div class="flex items-center mb-2">
            <div class="w-auto rounded-full text-center px-2 green text-white text-sm">
                £price
            </div>
        </div>

        <div class="flex items-center mb-3">
            <div class="w-auto rounded-full text-center px-2 yellow text-sm">
                Rooms left
            </div>
        </div>

        <div class="flex items-center mb-3 dark:text-white">
            <div class="availability-dot bg-gray-200" />
            <span class="ml-3">Sold out or unavailable</span>
        </div>

        <slot name="before-calendar" />

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

            <div>
                <div class="calendar-spinner" v-if="loading">
                    <easy-spinner type="spins" size="50" color="#22C55E" />
                </div>

                <Calendar
                    is-expanded
                    class="mb-6"
                    @update:from-page="(value) => updateFromPage(value, route)"
                >
                    <template v-slot:day-content="{ day, dayEvents }">
                        <div v-on="dayEvents">
                            <div class="flex justify-center">
                                {{ day.label }}
                            </div>
                            <div class="flex justify-center mb-4">
                                <div v-if="inPast(day.date, route)">-</div>

                                <div v-else-if="isAvailable(day.date, route)" class="dark:text-white">
                                    <div
                                        class="w-auto rounded-full text-center px-2 text-sm"
                                        :class="getPriceClass(day.date, route)"
                                    >
                                        <div>
                                            £{{ getPrice(day.date, route) }}
                                        </div>
                                    </div>
                                    <div
                                        class="w-auto rounded-full text-center px-2 mt-2 text-sm"
                                        :class="getRemainingClass(day.date, route)"
                                    >

                                        <div>{{getRemaining(day.date, route)}}</div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="
                                        availability-dot
                                        bg-gray-200
                                        text-sm
                                    "
                                >
                                </div>
                            </div>
                        </div>
                    </template>
                </Calendar>
            </div>

            <hr v-if="index === 0" class="mb-4" />
        </div>
    </div>
</template>

<style scoped>
.calendar-spinner {
    z-index: 9999;
    position: absolute;
    top: 57%;
    left: 48%;
}

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

.red {
  background-color: #DB4325;
}

.orange {
  background-color: #EDA247;
}

.yellow {
  background-color: #FFC20A;
}

.green {
  background-color: #57C4AD;
}

.blue {
  background-color: #0571B0;
}
</style>
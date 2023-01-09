<script setup>
import "v-calendar/dist/style.css";
import axios from "axios";
import { computed, ref, watch } from "@vue/runtime-core";
import { usePage } from "@inertiajs/inertia-vue3";
import Pulse from "./Pulse.vue";

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

const jobStatus = usePage().props.value.jobStatus;

const isDarkMode = ref(false);
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");
isDarkMode.value = darkTheme.matches;

const dates = ref({
    ABZ: [],
    LSI: [],
    KOI: [],
});

const {departureAirport, arrivalAirport} = props.routePayload;

const loading = ref(false);

const viewingMonth = ref(null);
const viewingYear = ref(null);

const requestData = async (month, year) => {
    loading.value = true;
    let params = "";

    if (props.routePayload) {
        params = `?${new URLSearchParams(props.routePayload).toString()}`;
    }

    viewingMonth.value = month;
    viewingYear.value = year;

    const { data } = await axios.get(
        `/api/${props.apiRoute}/${month}/${year}/${departureAirport}/${arrivalAirport}`
    );

    console.log(data);
    dates.value[departureAirport] = data[departureAirport];

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

    return foundDate.price
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
        ABZ: [],
        LSI: [],
        KOI: [],
    };

    dates.value.ABZ.filter((item) => item.available).forEach((item) => {
        prices.ABZ.push(item.price);
    });

    prices.ABZ = [...new Set(prices.ABZ)];

    dates.value.LSI.filter((item) => item.available).forEach((item) => {
        prices.LSI.push(item.price);
    });

    prices.LSI = [...new Set(prices.LSI)];

    dates.value.KOI.filter((item) => item.available).forEach((item) => {
        prices.KOI.push(item.price);
    });

    prices.KOI = [...new Set(prices.KOI)];

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
};

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

    return "blue text-white";
};

const isToday = (day) => {
    const today = new Date();
    const date = new Date(day.id);

    return (
        date.getFullYear() === today.getFullYear() &&
        date.getMonth() === today.getMonth() &&
        date.getDate() === today.getDate()
    );
};

watch(
    () => props.routePayload,
    () => requestData(viewingMonth.value, viewingYear.value)
);
</script>

<template>
    <div>
        <div class="sm:flex justify-between sm:pt-0 mb-4">
            <h1 class="text-4xl mb-2 md:mb-0 text-gray-600 dark:text-white">
                {{ title }}
            </h1>

            <div v-if="jobStatus.lastFetched" class="text-sm dark:text-white">
                <p>Last fetched: {{ jobStatus.lastFetched }}</p>
                <div
                    class="flex items-center mt-2"
                    v-if="jobStatus.currentlyRunning"
                >
                    <span>currently syncing</span>
                    <Pulse class="ml-4" />
                </div>
            </div>
        </div>

        <p class="mb-2 dark:text-white text-md">
            {{ description }}
        </p>

        <p class="mb-3 dark:text-white">
            Please note: This data is for information and should not be fully
            relied upon for trip planning. We attempt to sync data every hour.
        </p>

        <div class="flex items-center mb-3 dark:text-white">
            <div class="availability-dot bg-gray-200" />
            <span class="ml-3">Sold out or unavailable</span>
        </div>

        <slot name="before-calendar" />

        <hr class="my-4" />

        <div>
            <div class="calendar-spinner" v-if="loading">
                <easy-spinner type="spins" size="50" color="#22C55E" />
            </div>

            <Calendar
                is-expanded
                :is-dark="isDarkMode"
                class="mb-6"
                @update:from-page="(value) => updateFromPage(value, departureAirport)"
            >
                <template v-slot:day-content="{ day, dayEvents }">
                    <div v-on="dayEvents">
                        <div class="flex justify-center">
                            <span v-if="!isToday(day)">{{ day.label }}</span>
                            <div v-else class="today">{{ day.label }}</div>
                        </div>
                        <div class="flex justify-center mb-4">
                            <div v-if="inPast(day.date, departureAirport)">-</div>

                            <div
                                v-else-if="isAvailable(day.date, departureAirport)"
                                class="dark:text-white"
                            >
                                <div
                                    class="
                                        w-auto
                                        rounded-full
                                        text-center
                                        px-2
                                        text-sm
                                    "
                                    :class="getPriceClass(day.date, departureAirport)"
                                >
                                    <div>
                                        £{{ getPrice(day.date, departureAirport) }}
                                    </div>
                                </div>
                                <div
                                    class="
                                        w-auto
                                        rounded-full
                                        text-center
                                        px-2
                                        mt-2
                                        text-sm
                                    "
                                    :class="
                                        getRemainingClass(day.date, departureAirport)
                                    "
                                >
                                    <div>
                                        {{ getRemaining(day.date, departureAirport) }}
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="availability-dot bg-gray-200 text-sm"
                            ></div>
                        </div>
                    </div>
                </template>
            </Calendar>
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
    background-color: #db4325;
}

.orange {
    background-color: #eda247;
}

.yellow {
    background-color: #ffc20a;
}

.green {
    background-color: #57c4ad;
}

.blue {
    background-color: #0571b0;
}

/* .pulsating-circle {
  width: 12px;
  height: 12px;
}
.pulsating-circle:before {
  content: "";
  position: relative;
  display: block;
  width: 300%;
  height: 300%;
  box-sizing: border-box;
  margin-left: -100%;
  margin-top: -100%;
  border-radius: 45px;
  background-color: #01a4e9;
  -webkit-animation: pulse-ring 1.25s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
          animation: pulse-ring 1.25s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
.pulsating-circle:after {
  content: "";
  position: absolute;
  left: 0;
  top: 0;
  display: block;
  width: 100%;
  height: 100%;
}

@-webkit-keyframes pulse-ring {
  0% {
    transform: scale(0.33);
  }
  80%, 100% {
    opacity: 0;
  }
}

@keyframes pulse-ring {
  0% {
    transform: scale(0.33);
  }
  80%, 100% {
    opacity: 0;
  }
}
@-webkit-keyframes pulse-dot {
  0% {
    transform: scale(0.8);
  }
  50% {
    transform: scale(1);
  }
  100% {
    transform: scale(0.8);
  }
}
@keyframes pulse-dot {
  0% {
    transform: scale(0.8);
  }
  50% {
    transform: scale(1);
  }
  100% {
    transform: scale(0.8);
  }
} */
</style>
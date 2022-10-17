<script setup>
import { ref, computed } from "vue";
import { Head, useForm, Link } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";
import AvailabilityCalendar from "../components/AvailabilityCalendar.vue";

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

// const today = new Date();
// const month = today.getMonth() + 1;
// const year = today.getFullYear();

// getPetCabinData(month, year);

// const getAvailabilityClass = (date, route) => {
//     const year = date.getFullYear();
//     const month = date.getMonth() + 1;
//     const day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

//     const formattedDate = `${year}-${month}-${day}`;

//     const foundDate = dates.value[route].find(
//         (item) => item.date === formattedDate
//     );

//     if (!foundDate) {
//         return "bg-gray-200";
//     }

//     if (foundDate.past) return "bg-indigo-300";

//     return foundDate.available ? "bg-green-500" : "bg-red-500";
// };

// const updateFromPage = ({ month, year }, route) => {
//     getPetCabinData(month, year, route);
// };
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
        <Container>
            <AvailabilityCalendar
                :start-date="new Date(2022, 9, 1)"
                :request-data="getPetCabinData"
                :dates="dates"
                title="Pet cabin availabililty"
                description="This tool helps to determine pet cabin availability on the
                Lerwick/Aberdeen routes."
            />
        </Container>
    </div>
</template>

<style scoped>
.availability-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
</style>
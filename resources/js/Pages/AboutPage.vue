<script setup>
import { ref, computed, onMounted } from "vue";
import { Head, useForm, Link } from "@inertiajs/inertia-vue3";
import Container from "../components/Container.vue";
import NavBar from "../components/NavBar.vue";

const explain = ref("simple");

const changeExplain = (value) => {
    explain.value = value;
};

onMounted(() => {
    const encEmail = "amFtZXNAamFzdGV3YXJ0LmNvLnVr=";
    const form = document.getElementById("contact");
    form.setAttribute(
        "href",
        "mailto:".concat(Buffer.from(encEmail, "base64"))
    );
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
        <Container>
            <div
                class="
                    flex
                    justify-center
                    sm:pt-8 sm:justify-start sm:pt-0
                    mb-4
                "
            >
                <h1 class="text-4xl text-gray-600 dark:text-gray-200">About</h1>
            </div>

            <div class="dark:text-white">
                <p class="mb-2">
                    Hello! I'm
                    <a
                        class="underline"
                        href="https://www.twitter.com/JAStewart"
                    >
                        James Stewart</a
                    >, an ex-pat Shetlander living in Ayrshire working as a
                    software engineer.
                </p>

                <p class="mb-2">
                    Northlink scanner is a project I have created to make it
                    easier to get information about the Northlink ferry service,
                    which operates between the North of Scotland, Shetland and
                    Orkney. As of December 2022, the site only shows information
                    about the Lerwick to Aberdeen route, but the Orkney routes
                    will probably be added in the future.
                </p>

                <p class="mb-4">
                    The idea for this project came from complaints made by my
                    wife and brother separately about the difficulty in trying
                    to find available pet cabins when traveling back north to
                    see family. The process of booking with Northlink's booking
                    system can be
                    <a href="https://streamable.com/dprxym" class="underline"
                        >painful and slow.</a
                    >
                </p>

                <p class="mb-4">
                    If you work for Northlink,
                    <a id="contact" href="" class="underline"
                        >please reach out to me</a
                    >! The purpose of this project is not to replace the booking
                    website, nor antagonise the company, but to make it easier
                    for people to get information about the service. We could
                    make it better if we work together.
                </p>
            </div>

            <div class="mb-4 border p-4">
                <p class="dark:text-white mb-4 text-lg font-bold">
                    How does it work?
                </p>
                <div class="flex mb-4">
                    <button
                        class="
                            bg-blue-500
                            hover:bg-green-500
                            text-white
                            font-bold
                            py-2
                            px-4
                            rounded
                            mr-2
                        "
                        :class="{ 'bg-green-500': explain === 'simple' }"
                        @click="changeExplain('simple')"
                    >
                        Give it to me straight
                    </button>
                    <button
                        class="
                            bg-blue-500
                            hover:bg-green-500
                            text-white
                            font-bold
                            py-2
                            px-4
                            rounded
                        "
                        :class="{ 'bg-green-500': explain === 'technical' }"
                        @click="changeExplain('technical')"
                    >
                        More details please!
                    </button>
                </div>
                <p v-if="explain === 'simple'">
                    Northlink has publically accessible data which I have
                    grabbed and collated. No hacking or security breaches were
                    involved! I sync my data every 15 minutes with the Northlink
                    data.
                </p>
                <div v-else>
                    <p class="mb-3">
                        Northlink uses booking software called BOOKIT. I noticed
                        that their booking process is handled via a REST API
                        which is publicly accessible. I examined the payloads
                        used as part of and found that I could use the tokens
                        that get passed between the client and server to be able
                        to pull data.
                    </p>
                    <p class="mb-3">
                        I started building jobs to pull the data through trial
                        and error. At the moment, I run three jobs - one that
                        simulates a booking for 1 person, one that simulates a
                        booking for 2 people and a car, and one which pulls all
                        available accommodations. The first two allow me to
                        answer surface level questions like "does this date have
                        car space" while the accommodations one allows me to
                        answer more complex questions like "how many cabins are
                        available on this date" and "what is the price for the
                        cabin?".
                    </p>
                    <p class="mb-3">
                        The site is built in Laravel and Vue using Inertia. The
                        three jobs are combined into one single job which runs
                        every 15 minutes via a CRON job through Laravel Forge. I
                        store the data in a MySQL database (rather than pulling
                        live data) for the sake of latency.
                    </p>
                </div>
            </div>

            <hr class="my-4" />
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
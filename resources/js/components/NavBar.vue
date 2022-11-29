<script setup>
import { Inertia } from "@inertiajs/inertia";
import { Link, useForm, usePage } from "@inertiajs/inertia-vue3";
import JetDropdown from "@/Jetstream/Dropdown.vue";
import JetDropdownLink from "@/Jetstream/DropdownLink.vue";
import { ref } from "vue";
import Hamburger from "./icons/Hamburger.vue";
import MobileMenu from "./MobileMenu.vue";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <header
        class="
            border-b
            lg:flex
            md:items-center md:justify-between
            p-4
            pb-0
            shadow-lg
            md:pb-4
            bg-gray-100
            dark:bg-gray-800
        "
    >
        <div class="flex items-center justify-between mb-4 md:mb-0">
            <div class="flex items-center">
                <h1 class="leading-none text-2xl text-grey-darkest">
                    <Link
                        class="
                            no-underline
                            text-grey-darkest
                            hover:text-black
                            dark:text-white
                        "
                        :href="route('home')"
                    >
                        <span class="dark:text-white"> Northlink Scanner </span>
                    </Link>
                </h1>
            </div>

            <Hamburger
                class="md:flex lg:hidden"
                :showing-navigation-dropdown="showingNavigationDropdown"
                @toggle="showingNavigationDropdown = !showingNavigationDropdown"
            />
        </div>

        <MobileMenu
            :showing-navigation-dropdown="showingNavigationDropdown"
            class="lg:hidden"
        />

        <nav class="hidden lg:flex sm:justify-center">
            <ul
                class="list-reset md:flex md:items-center"
                style="width: max-content"
            >
                <!-- <li class="md:ml-4 my-2">
                    <Link
                        class="
                            border-t
                            block
                            no-underline
                            hover:underline
                            py-2
                            text-grey-darkest
                            hover:text-black
                            md:border-none md:p-0
                            dark:text-white dark:hover:text-white
                        "
                        :href="route('pets')"
                        :class="{ 'font-bold': $page.url === '/pets' }"
                    >
                        Pet Cabin Availability
                    </Link>
                </li>

                <li class="md:ml-4 my-2">
                    <Link
                        class="
                            border-t
                            block
                            no-underline
                            hover:underline
                            py-2
                            text-grey-darkest
                            hover:text-black
                            md:border-none md:p-0
                            dark:text-white dark:hover:text-white
                        "
                        :href="route('capacity')"
                        :class="{ 'font-bold': $page.url === '/capacity' }"
                    >
                        Capacity
                    </Link> -->
                <!-- </li> -->
                <!-- <JetDropdown align="right" width="48">
                    <template #trigger>
                        <button
                            type="button"
                            class="
                                ml-2
                                border-t
                                flex
                                no-underline
                                hover:underline
                                py-2
                                text-grey-darkest
                                hover:text-black
                                md:border-none md:p-0
                                dark:text-white dark:hover:text-white
                            "
                        >
                            Categories

                            <svg
                                class="ml-2 -mr-0.5 h-6 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <JetNavLink
                            class="cursor-pointer m-2 mb-2"
                            :active="route().current('categories')"
                        >
                            Ingredient Categories
                        </JetNavLink>
                        <JetNavLink
                            class="cursor-pointer m-2 mb-2"
                            :active="route().current('recipe.categories')"
                        >
                            Recipe Categories
                        </JetNavLink>
                    </template>
                </JetDropdown> -->

                <!-- <li class="md:ml-4 my-2">
                    <Link
                        class="
                            border-t
                            block
                            no-underline
                            hover:underline
                            py-2
                            text-grey-darkest
                            hover:text-black
                            md:border-none md:p-0
                            dark:text-white dark:hover:text-white
                        "
                        :class="{ 'font-bold': $page.url === '/ingredients' }"
                    >
                        Ingredients
                    </Link>
                </li>
                <li class="md:ml-4 my-2">
                    <Link
                        class="
                            border-t
                            block
                            no-underline
                            hover:underline
                            py-2
                            text-grey-darkest
                            hover:text-black
                            md:border-none md:p-0
                            dark:text-white dark:hover:text-white
                        "
                        :class="{ 'font-bold': $page.url === '/recipes' }"
                    >
                        Recipes
                    </Link>
                </li> -->
            </ul>
        </nav>
    </header>
</template>

<style scoped>
input {
    width: 100%;
}

.nav-link:hover {
    background-color: rgb(19 79 230);
    color: white;
}

.nav-link {
    cursor: pointer;
    display: flex;
    justify-content: center;
    width: 100%;
    align-items: center;
    border-bottom-width: 1px;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    color: black;
    background-color: rgb(241 241 241);
}

.random {
    background-color: #ffe368;
    margin-bottom: 3px;
    cursor: pointer;
}
</style>
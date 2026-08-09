<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';


// import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
defineProps({
    title: String,
});



const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};
let contentState = true;
const toggleSidebar = () => {

    if (contentState) {
        document.getElementById('sidebar-dev').classList.add('d-none');

        document.getElementById('content-dev').classList.remove('col-10');
        document.getElementById('content-dev').classList.add('col-12');
        contentState = false;
    }
    else {
        document.getElementById('sidebar-dev').classList.remove('d-none');

        document.getElementById('content-dev').classList.add('col-10');
        document.getElementById('content-dev').classList.remove('col-12');
        contentState = true;

    }
}
const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>

        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100" style="position: fixed; z-index: 1; width: 100%; border: none ;box-shadow: -34px 19px 34px -15px #000000;
  -webkit-box-shadow: -34px 19px 34px -15px #000000;
  -moz-box-shadow: -34px 19px 34px -15px #000000;
">

                <!-- Primary Navigation Menu -->
                <div class="max-w-12xl mx-auto px-4 sm:px-6 lg:px-12 bg-darksilver"
                    style="direction: rtl; height: 69px;">

                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <!-- <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                <ApplicationMark class="block h-9 w-auto"
                                    style="width: 60px !important; height: 60px !important;" />
                                الرئيسية
                                </Link>
                            </div> -->

                            <!-- Navigation Links -->
                            <button class="btn bnt-sm" @click="toggleSidebar()">
                                <FontAwesomeIcon icon="bars" class="text-blue-600" />


                            </button>

                        </div>
                        <h1 class="d-flex align-self-center font-weight-bold col-10 px-5" style="font-size: 1.5rem;">
                            منظومة طلبات الحالة الجنائية </h1>
                        <div class="hidden sm:flex sm:items-center sm:ms-6 m-0">
                            <div class="ms-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150 "
                                                style="background-color: #191c24 !important; color: white !important;">
                                                {{ $page.props.auth.user.current_team.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink
                                                :href="route('teams.show', $page.props.auth.user.current_team)">
                                                Team Settings
                                            </DropdownLink>

                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams"
                                                :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                <div class="border-t border-gray-200" />

                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Switch Teams
                                                </div>

                                                <template v-for="team in $page.props.auth.user.all_teams"
                                                    :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                                    class="me-2 h-5 w-5 text-green-400"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>

                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos"
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="h-8 w-8 rounded-full object-cover"
                                                :src="$page.props.auth.user.profile_photo_url"
                                                :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button"
                                                class="btn btn-outline-primary mt-3  d-flex align-items-center justify-content-center text-white"
                                                style="width: 6rem; height: 3rem; font-size: 0.8rem; ">
                                                {{ $page.props.auth.user.name }}

                                                <svg class=" ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400" style="left: 10px;">
                                            ــــــــ
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            اعدادات
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures"
                                            :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                تسجيل الخروج
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                                @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="h-10 w-10 rounded-full object-cover"
                                    :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200" />

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)"
                                    :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams"
                                    :href="route('teams.create')" :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200" />

                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                        class="me-2 h-5 w-5 text-green-400"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow" style="height: 64px;">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <div class="col-12 row " style="background-color: #000000;">

                <main class="col-10 " id="content-dev">

                    <slot />
                </main>
                <div id="sidebar-dev" class="col-2  bg-primary p-0 h-100 " style="position: fixed;
  right: 0px;">
                    <div class="d-flex flex-column flex-shrink-0 p-3 pt-4 bg-light h-100 bg-darksilver">

                        <ul class=" col-12 d-flex flex-row align-items-center justify-content-between">
                            <FontAwesomeIcon icon="ellipsis-vertical" class="text-danger p-0 m-0" />

                            <label style="font-size: 0.9rem ;">نظام الحالة الجنائية


                            </label>
                            <li class="nav-item">
                                <ApplicationMark class="" style="width: 35px !important; height: 35px !important;" />
                            </li>

                        </ul>

                        <ul class="nav nav-pills flex-column mb-auto">



                            <li class="nav-item">
                                <Link :href="route('users-index')"
                                    :class="$inertia.page.component == 'Users' ? 'nav-link active' : 'nav-link '"
                                    aria-current="page">
                                    <svg class="bi me-2 d-inline" width="16" height="16">
                                        <use xlink:href="#home"></use>
                                    </svg>
                                    <label for="">
                                        إدارة المستخدمين</label>
                                </Link>
                            </li>


                            <li class="nav-item">
                                <Link :href="route('rejected-personal-pictures-index')"
                                    :class="$inertia.page.component == 'RejectedPersonalPictures' ? 'nav-link active' : 'nav-link '"
                                    aria-current="page">
                                    <svg class="bi me-2 d-inline" width="16" height="16">
                                        <use xlink:href="#home"></use>
                                    </svg>
                                    <label for=""> الصور الخاطئة
                                    </label>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('waiting-for-search')"
                                    :class="$inertia.page.component == 'WaitingForSearch' ? 'nav-link active' : 'nav-link '"
                                    aria-current="page">
                                    <svg class="bi me-2 d-inline" width="16" height="16">
                                        <use xlink:href="#home"></use>
                                    </svg>
                                    <label for=""> في انتظار البحث
                                    </label>
                                </Link>
                            </li>

                            <li class="nav-item">
                                <Link :href="route('booked-for-searchers')"
                                    :class="$inertia.page.component == 'BookedForSearchers' ? 'nav-link active' : 'nav-link '"
                                    aria-current="page">
                                    <svg class="bi me-2 d-inline" width="16" height="16">
                                        <use xlink:href="#home"></use>
                                    </svg>
                                    <label for=""> محجوز للباحثين
                                    </label>
                                </Link>
                            </li>

                            <li class="nav-item">
                                <Link :href="route('criminal-record-office')"
                                    :class="$inertia.page.component == 'CriminalRecordOffice' ? 'nav-link active' : 'nav-link '"
                                    aria-current="page">
                                    <svg class="bi me-2 d-inline" width="16" height="16">
                                        <use xlink:href="#home"></use>
                                    </svg>
                                    <label for=""> مكتب السوابق
                                    </label>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('waiting-for-approval')"
                                    :class="$inertia.page.component == 'WaitingForApproval' ? 'nav-link active' : 'nav-link '"
                                    aria-current="page">
                                    <svg class="bi me-2 d-inline" width="16" height="16">
                                        <use xlink:href="#home"></use>
                                    </svg>
                                    <label for=""> طلبات محالة الاعتماد
                                    </label>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('ready-requests')"
                                    :class="$inertia.page.component == 'ReadyRequests' ? 'nav-link active' : 'nav-link '"
                                    aria-current="page">
                                    <svg class="bi me-2 d-inline" width="16" height="16">
                                        <use xlink:href="#home"></use>
                                    </svg>
                                    <label for=""> جاهزة للاستلام
                                    </label>
                                </Link>
                            </li>

                        </ul>
                        <hr>

                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<style>
.gray-bg {
    background-color: #2c3034 !important;
}

* {
    text-align: right;

}

input::placeholder {
    color: #d3d4d5 !important;
    opacity: 0.1;
}

input {
    background-color: #2a3038 !important;
    border: none !important;
    color: white !important;
}

select {
    background-color: #2a3038 !important;
    border: none !important;
    color: white !important;
}

.spinner-icon {
    width: 70px !important;
    height: 70px !important;
}

#nprogress .spinner {
    top: 50% !important;
    right: 50% !important;
}

.bg-darksilver {
    background-color: #191c24 !important;
}

* {
    color: white;
}

.el-card__body {
    background-color: #191c24 !important;
    border: unset !important;
}

.el-card {
    border-color: unset;
    border-radius: 10px;
    border: unset;
}

.el-table__empty-block {
    background-color: #000000;
}

th {
    background-color: #191c24 !important;
    border-color: unset !important;
    border: unset !important;
}

.el-table__inner-wrapper::before {
    height: unset;
}

.el-table--border::after {
    top: unset;
}

/* .el-table--border::before {
    width: unset !important;
} */
.el-table {
    --el-table-border-color: unset;
}

.min-h-screen {
    background-color: black;
}

.el-table__inner-wrapper {
    z-index: 0;
}

.el-pager li {
    background-color: #000000;
    color: white !important;
}

.btn-next .el-icon svg path {
    color: #000000 !important;
}

.btn-prev .el-icon svg path {
    color: #000000 !important;
}

.nav-link.active {
    background: #0f1015 !important;
    color: white;
}

.nav-link {

    color: white;
}

.nav-item {
    color: #d9d9d9 !important;
}

.el-pagination .btn-prev .el-icon,
.el-pagination .btn-next .el-icon {
    display: none !important;
}

.el-pagination .btn-prev::after {
    content: "السابق <";
}

.el-pagination .btn-next::after {
    content: "التالي>";
}

.el-overlay-dialog {
    background: #afafaf24 !important;
}

.el-dialog {
    background: #191c24 !important;
    box-shadow: 0px 0px 8px #ffffff21;
    border: solid 0.1px #4a4a4a;
}

.el-dialog__header {
    direction: rtl;
    padding: 0px !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.el-dialog__title {
    color: white;
}

.el-dialog__headerbtn {

    position: inherit;
}
</style>

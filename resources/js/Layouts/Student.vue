<template>
    <nav class="navbar navbar-expand-lg navbar-transparent navbar-dark navbar-theme-primary mb-4 shadow">
        <div class="container position-relative">
            <Link class="navbar-brand me-lg-3" href="/student/dashboard">
                <img class="navbar-brand-dark" src="/assets/images/logo.png" style="height:70px">
            </Link>
            <button
                class="navbar-toggler border-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                </ul>
                <form class="d-flex mt-3 mt-lg-0" v-if="$page.props.auth.student">
                    <Link href="/logout" method="POST" class="btn btn-secondary shadow w-100 w-lg-auto" as="button">LOGOUT</Link>
                </form>
            </div>
        </div>
    </nav>
    <div class="container">
        <slot />
    </div>
</template>

<script>

    //import Link
    import { Link } from '@inertiajs/vue3';

    export default {

        //register components
        components: {
            Link
        },

    }

</script>

<style>

</style>

<template>
    <Head title="404 • Halaman Tidak Ditemukan" />
    <div class="min-h-screen bg-slate-950 text-white flex items-center justify-center relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -right-24 h-80 w-80 rounded-full bg-sky-400/30 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-20 h-96 w-96 rounded-full bg-violet-500/30 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[28rem] w-[28rem] rounded-full bg-sky-500/10 blur-[10rem]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.12)_0,_rgba(15,23,42,0)_70%)]"></div>
        </div>

        <div class="relative z-10 max-w-2xl px-6 text-center">
            <div class="flex items-center justify-center gap-3 mb-8">
                <span class="text-xs tracking-[0.5em] uppercase text-sky-300/80">Oops</span>
                <span class="h-px w-12 bg-sky-500/50"></span>
                <span class="text-xs tracking-[0.5em] uppercase text-white/60">Error</span>
            </div>

            <h1 class="text-7xl md:text-8xl font-black leading-none sm:leading-tight bg-clip-text text-transparent bg-gradient-to-r from-sky-300 via-white to-violet-300 drop-shadow-lg">
                404
            </h1>

            <p class="mt-6 text-2xl md:text-3xl font-semibold text-white/90">
                Halaman yang kamu cari tidak ditemukan.
            </p>
            <p class="mt-4 text-base md:text-lg text-slate-200/70 max-w-xl mx-auto">
                Mungkin tautan sudah dipindahkan atau kamu salah ketik alamat. Yuk kembali ke halaman utama dan lanjutkan aktivitasmu.
            </p>

            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <button
                    type="button"
                    @click="goBack"
                    class="group inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/10 transition hover:border-white/40 hover:bg-white/10 hover:text-white"
                >
                    <span class="mr-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-xs group-hover:bg-white/20">&larr;</span>
                    Kembali
                </button>

                <Link
                    v-if="homeUrl"
                    :href="homeUrl"
                    class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-sky-400 via-sky-500 to-teal-400 px-5 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-sky-500/30 transition hover:from-sky-300 hover:via-sky-400 hover:to-teal-300"
                >
                    Ke Beranda
                </Link>

                <Link
                    v-if="adminUrl"
                    :href="adminUrl"
                    class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-500/20 transition hover:border-white/30 hover:bg-white/10"
                >
                    Panel Admin
                </Link>
            </div>

            <div class="mt-14 flex flex-wrap items-center justify-center gap-6 text-sm text-white/60">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full bg-green-400"></span>
                    Sistem berjalan normal
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full bg-sky-400"></span>
                    Status kode: {{ status }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        default: 404,
    },
    homeUrl: {
        type: String,
        default: '/',
    },
    adminUrl: {
        type: String,
        default: null,
    },
});

const goBack = () => {
    if (typeof window !== 'undefined' && window.history.length > 1) {
        window.history.back();
        return;
    }

    router.visit(props.homeUrl ?? '/');
};
</script>

<style scoped>
button span {
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
</style>

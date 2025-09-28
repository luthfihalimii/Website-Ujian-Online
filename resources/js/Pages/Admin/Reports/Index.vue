<template>
    <Head>
        <title>Laporan Nilai Ujian - Aplikasi Ujian Online</title>
    </Head>
    <div class="container-fluid mb-5 mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow mb-4">
                    <div class="card-body">
                        <h5><i class="fa fa-filter"></i> Filter Nilai Ujian</h5>
                        <hr>
                        <form @submit.prevent="filter">
                            
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="control-label" for="name">Ujian</label>
                                    <select class="form-select" v-model="form.exam_id">
                                        <option value="" disabled>Pilih ujian</option>
                                        <option v-for="exam in exams" :key="exam.id" :value="exam.id.toString()">
                                            {{ exam.title }} — Kelas : {{ exam.classroom.title }} — Pelajaran : {{ exam.lesson.title }}
                                        </option>
                                    </select>
                                    <div v-if="errors.exam_id" class="alert alert-danger mt-2">
                                        {{ errors.exam_id }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="control-label" for="session">Sesi Ujian</label>
                                    <select id="session" class="form-select" v-model="form.exam_session_id" :disabled="sessionOptions.length === 0">
                                        <option v-if="sessionOptions.length === 0" value="">Tidak ada sesi tersedia</option>
                                        <option v-for="session in sessionOptions" :key="session.id" :value="session.id.toString()">
                                            {{ session.title }} — {{ session.start_time }} s/d {{ session.end_time }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-md btn-primary border-0 shadow w-100" :disabled="!form.exam_id">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>

                            <div v-if="form.exam_id && sessionOptions.length === 0" class="alert alert-warning border-0 shadow-sm mt-3">
                                <i class="fa fa-info-circle"></i> Tidak ada sesi terjadwal untuk ujian ini.
                            </div>

                        </form>
                    </div>
                </div>

                <div v-if="grades.length > 0" class="card border-0 shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9 col-12">
                                <h5 class="mt-2"><i class="fa fa-chart-line"></i> Laporan Nilai Ujian</h5>
                            </div>
                            <div class="col-md-3 col-12">
                                <a v-if="exportUrl" :href="exportUrl" target="_blank" class="btn btn-success btn-md border-0 shadow w-100 text-white">
                                    <i class="fa fa-file-excel"></i> Download Excel
                                </a>
                                <button v-else type="button" class="btn btn-success btn-md border-0 shadow w-100 text-white" disabled>
                                    <i class="fa fa-file-excel"></i> Download Excel
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center mb-4">
                            <div class="col-6 col-lg-3 mb-3">
                                <div class="border rounded py-3 shadow-sm h-100">
                                    <h6 class="text-muted mb-1">Peserta</h6>
                                    <h4 class="fw-bold mb-0">{{ summary.participants }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 mb-3">
                                <div class="border rounded py-3 shadow-sm h-100">
                                    <h6 class="text-muted mb-1">Nilai Rata-rata</h6>
                                    <h4 class="fw-bold mb-0">{{ summary.avg_grade }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 mb-3">
                                <div class="border rounded py-3 shadow-sm h-100">
                                    <h6 class="text-muted mb-1">Selesai</h6>
                                    <h4 class="fw-bold mb-0">{{ summary.completed }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 mb-3">
                                <div class="border rounded py-3 shadow-sm h-100">
                                    <h6 class="text-muted mb-1">Kecurangan</h6>
                                    <h4 class="fw-bold mb-0">{{ summary.cheat_warnings }} / {{ summary.cheat_locked }}</h4>
                                    <small class="text-muted">Peringatan / Dikunci</small>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-centered table-nowrap mb-0 rounded">
                                <thead class="thead-dark">
                                    <tr class="border-0">
                                        <th class="border-0 rounded-start" style="width:5%">No.</th>
                                        <th class="border-0">Ujian</th>
                                        <th class="border-0">Sesi</th>
                                        <th class="border-0">Nama Siswa</th>
                                        <th class="border-0">Kelas</th>
                                        <th class="border-0">Pelajaran</th>
                                        <th class="border-0">Status Kecurangan</th>
                                        <th class="border-0 rounded-end">Nilai</th>
                                    </tr>
                                </thead>
                                <div class="mt-2"></div>
                                <tbody>
                                    <tr v-for="(grade, index) in grades" :key="grade.id">
                                        <td class="fw-bold text-center">
                                            {{ index + 1 }}
                                        </td>
                                        <td>{{ grade.exam.title }}</td>
                                        <td>{{ grade.exam_session.title }}</td>
                                        <td>{{ grade.student.name }}</td>
                                        <td class="text-center">{{ grade.exam.classroom.title }}</td>
                                        <td>{{ grade.exam.lesson.title }}</td>
                                        <td class="text-center">
                                            <span v-if="grade.cheat_status === 'LOCKED'" class="badge bg-danger">Dikunci</span>
                                            <span v-else-if="grade.cheat_status === 'WARNED'" class="badge bg-warning text-dark">Diperingatkan</span>
                                            <span v-else class="badge bg-success">Aman</span>
                                            <div v-if="grade.cheat_count" class="small text-muted mt-1">
                                                {{ grade.cheat_count }}x — {{ grade.last_cheat_at ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="fw-bold text-center">{{ grade.grade }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    //import layout Admin
    import LayoutAdmin from '../../../Layouts/Admin.vue';

    //import Head from Inertia
    import {
        Head,
        router
    } from '@inertiajs/vue3';

    //import reactive from vue
    import { reactive, computed, watch } from 'vue';

    export default {

        //layout
        layout: LayoutAdmin,

        //register components
        components: {
            Head,
        },

        //props
        props: {
            errors: Object,
            exams: Array,
            grades: Array,
            sessions: Array,
            filters: Object,
            summary: Object,
        },

        //inisialisasi composition API
        setup(props) {

            const form = reactive({
                exam_id: props.filters?.exam_id ? props.filters.exam_id.toString() : '',
                exam_session_id: props.filters?.exam_session_id ? props.filters.exam_session_id.toString() : '',
            });

            const sessionOptions = computed(() => {
                if (!form.exam_id) {
                    return [];
                }

                const examId = Number(form.exam_id);
                return props.sessions.filter((session) => session.exam_id === examId);
            });

            watch(() => form.exam_id, () => {
                const options = sessionOptions.value;

                if (!form.exam_id || options.length === 0) {
                    form.exam_session_id = '';
                    return;
                }

                const exists = options.some((option) => option.id.toString() === form.exam_session_id);
                if (!exists) {
                    form.exam_session_id = options[0].id.toString();
                }
            }, { immediate: true });

            const exportUrl = computed(() => {
                if (!form.exam_id) {
                    return null;
                }

                const params = new URLSearchParams({ exam_id: form.exam_id });
                if (form.exam_session_id) {
                    params.append('exam_session_id', form.exam_session_id);
                }

                return `/admin/reports/export?${params.toString()}`;
            });

             //define methods filter
            const filter = () => {
                if (!form.exam_id) {
                    return;
                }

                router.get('/admin/reports/filter', {
                    exam_id: form.exam_id,
                    exam_session_id: form.exam_session_id,
                }, {
                    preserveScroll: true,
                });
            }

            //return
            return {
                form,
                filter,
                sessionOptions,
                exportUrl,
            }

        }

    }

</script>

<style>

</style>

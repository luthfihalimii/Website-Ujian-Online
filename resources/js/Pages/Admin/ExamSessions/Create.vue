<template>
    <Head>
        <title>Tambah Sesi Ujian - Aplikasi Ujian Online</title>
    </Head>
    <div class="container-fluid mb-5 mt-5">
        <div class="row">
            <div class="col-md-12">
                <Link href="/admin/exam_sessions" class="btn btn-md btn-primary border-0 shadow mb-3" type="button"><i
                    class="fa fa-long-arrow-alt-left me-2"></i> Kembali</Link>
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <h5><i class="fas fa-stopwatch"></i> Tambah Sesi</h5>
                        <hr>
                        <form @submit.prevent="submit">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label>Nama Sesi</label>
                                        <input type="text" class="form-control" placeholder="Masukkan Nama Sesi"
                                            v-model="form.title">
                                        <div v-if="errors.title" class="alert alert-danger mt-2">
                                            {{ errors.title }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label>Ujian</label>
                                        <select class="form-select" v-model="form.exam_id">
                                            <option v-for="(exam, index) in exams" :key="index" :value="exam.id">
                                                {{ exam.title }}</option>
                                        </select>
                                        <div v-if="errors.exam_id" class="alert alert-danger mt-2">
                                            {{ errors.exam_id }}
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label>Waktu Mulai</label>
                                        <Datepicker v-model="form.start_time" />
                                        <div v-if="errors.start_time" class="alert alert-danger mt-2">
                                            {{ errors.start_time }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label>Waktu Selesai</label>
                                        <Datepicker v-model="form.end_time" />
                                        <div v-if="errors.end_time" class="alert alert-danger mt-2">
                                            {{ errors.end_time }}
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <button type="submit" class="btn btn-md btn-primary border-0 shadow me-2">Simpan</button>
                            <button type="reset" class="btn btn-md btn-warning border-0 shadow">Reset</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    //import layout
    import LayoutAdmin from '../../../Layouts/Admin.vue';

    //import Heade and Link from Inertia
    import {
        Head,
        Link,
        router
    } from '@inertiajs/vue3';

    //import reactive from vue
    import {
        reactive
    } from 'vue';

    //import sweet alert2
    import Swal from 'sweetalert2';

    //import datepicker
    import Datepicker from '@vuepic/vue-datepicker';
    import '@vuepic/vue-datepicker/dist/main.css';

    export default {

        //layout
        layout: LayoutAdmin,

        //register components
        components: {
            Head,
            Link,
            Datepicker
        },

        //props
        props: {
            errors: Object,
            exams: Array,
        },

        //inisialisasi composition API
        setup() {

            //define form with reactive
            const form = reactive({
                title: '',
                exam_id: '',
                start_time: '',
                end_time: '',
            });

            //method "submit"
            const submit = () => {

                //send data to server
                router.post('/admin/exam_sessions', {
                    //data
                    title: form.title,
                    exam_id: form.exam_id,
                    start_time: form.start_time,
                    end_time: form.end_time,
                }, {
                    onSuccess: () => {
                        //show success alert
                        Swal.fire({
                            title: 'Success!',
                            text: 'Sesi Ujian Berhasil Disimpan!.',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    },
                });

            }

            //return
            return {
                form,
                submit,
            }

        }

    }

</script>

<style>

</style>
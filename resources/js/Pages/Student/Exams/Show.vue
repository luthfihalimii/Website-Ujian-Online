<template>
    <Head>
        <title>Ujian Dengan Nomor Soal : {{ page }} - Aplikasi Ujian Online</title>
    </Head>
    <div class="row mb-5">
        <div class="col-md-7">
            <div class="card border-0 shadow">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">Soal No. <strong class="fw-bold">{{ page }}</strong></h5>
                        </div>
                        <div>
                            <VueCountdown :time="duration" @progress="handleChangeDuration" @end="showModalEndTimeExam = true" v-slot="{ hours, minutes, seconds }">
                                <span class="badge bg-info p-2"> <i class="fa fa-clock"></i> {{ hours }} jam,
                                    {{ minutes }} menit, {{ seconds }} detik.</span>
                            </VueCountdown>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div v-if="question_active !== null">

                        <div>
                            <p v-html="question_active.question.question"></p>
                        </div>

                        <table>
                            <tbody>
                                <tr v-for="(answer, index) in answer_order" :key="index">
                                    <td width="50" style="padding: 10px;">

                                        <button v-if="answer == question_active.answer" class="btn btn-info btn-sm w-100 shdaow">{{ options[index] }}</button>

                                        <button v-else @click.prevent="submitAnswer(question_active.question.exam.id, question_active.question.id, answer)" class="btn btn-outline-info btn-sm w-100 shdaow">{{ options[index] }}</button>

                                    </td>
                                    <td style="padding: 10px;">
                                        <p v-html="question_active.question['option_'+answer]"></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <div v-else>
                        <div class="alert alert-danger border-0 shadow">
                            <i class="fa fa-exclamation-triangle"></i> Soal Tidak Ditemukan!.
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div class="text-start">
                            <button v-if="page > 1" @click.prevent="prevPage" type="button" class="btn btn-gray-400 btn-sm btn-block mb-2">Sebelumnya</button>
                        </div>
                        <div class="text-end">
                            <button v-if="page < Object.keys(all_questions).length" @click.prevent="nextPage" type="button" class="btn btn-gray-400 btn-sm">Selanjutnya</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow">
                <div class="card-header text-center">
                    <div class="badge bg-success p-2"> {{ question_answered }} dikerjakan</div>
                </div>
                <div class="card-body" style="height: 330px;overflow-y: auto">

                    <div v-for="(question, index) in all_questions" :key="index">
                        <div width="20%" style="width: 20%; float: left;">
                            <div style="padding: 5px;">

                                <button @click.prevent="clickQuestion(index)" v-if="index+1 == page" class="btn btn-gray-400 btn-sm w-100">{{ index + 1 }}</button>

                                <button @click.prevent="clickQuestion(index)" v-if="index+1 != page && question.answer == 0" class="btn btn-outline-info btn-sm w-100">{{ index + 1 }}</button>

                                <button @click.prevent="clickQuestion(index)" v-if="index+1 != page && question.answer != 0" class="btn btn-info btn-sm w-100">{{ index + 1 }}</button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button @click="showModalEndExam = true" class="btn btn-danger btn-md border-0 shadow w-100">Akhiri Ujian</button>
                </div>
            </div>
        </div>
    </div>

    <!-- modal akhiri ujian -->
    <div v-if="showModalEndExam" class="modal fade" :class="{ 'show': showModalEndExam }" tabindex="-1" aria-hidden="true" style="display:block;" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Akhiri Ujian ?</h5>
                </div>
                <div class="modal-body">
                    Setelah mengakhiri ujian, Anda tidak dapat kembali ke ujian ini lagi. Yakin akan mengakhiri ujian?
                </div>
                <div class="modal-footer">
                    <button @click.prevent="endExam" type="button" class="btn btn-primary">Ya, Akhiri</button>
                    <button @click.prevent="showModalEndExam = false" type="button" class="btn btn-secondary">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- modal waktu ujian berakhir -->
    <div v-if="showModalEndTimeExam" class="modal fade" :class="{ 'show': showModalEndTimeExam }" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true" style="display:block;" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Waktu Habis !</h5>
                </div>
                <div class="modal-body">
                    Waktu ujian sudah berakhir!. Klik <strong class="fw-bold">Ya</strong> untuk mengakhiri ujian.
                </div>
                <div class="modal-footer">
                    <button @click.prevent="endExam" type="button" class="btn btn-primary">Ya</button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import LayoutStudent from '../../../Layouts/Student.vue';

    import {
        Head,
        Link,
        router
    } from '@inertiajs/vue3';

    import { ref, onMounted, onBeforeUnmount } from 'vue';

    import VueCountdown from '@chenfengyuan/vue-countdown';

    import axios from 'axios';

    import Swal from 'sweetalert2';

    export default {
        layout: LayoutStudent,

        components: {
            Head,
            Link,
            VueCountdown
        },

        props: {
            id: Number,
            page: Number,
            exam_group: Object,
            all_questions: Array,
            question_answered: Number,
            question_active: Object,
            answer_order: Array,
            duration: Object,
        },

        setup(props) {
            const options = ["A", "B", "C", "D", "E"];

            const counter = ref(0);
            const duration = ref(props.duration?.duration ?? 0);

            const showModalEndExam = ref(false);
            const showModalEndTimeExam = ref(false);

            let locked = false;
            let durationFinalized = false;
            let checkDevtoolsInterval = null;
            let isPersistingDuration = false;
            let deviceChangeListener = null;
            let originalGetDisplayMedia = null;

            const deviceTokenKey = `exam_device_token_${props.exam_group?.id ?? 'unknown'}`;
            let initialToken = null;

            if (typeof window !== 'undefined') {
                try {
                    initialToken = window.localStorage.getItem(deviceTokenKey);
                } catch (_) {
                    initialToken = null;
                }
            }

            if (!initialToken && typeof window !== 'undefined') {
                const generated = window.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(16).slice(2)}`;
                initialToken = generated;
                try {
                    window.localStorage.setItem(deviceTokenKey, generated);
                } catch (_) {
                    // ignore storage issues
                }
            }

            const deviceToken = ref(initialToken || `${Date.now()}-${Math.random().toString(16).slice(2)}`);

            const deviceInfo = typeof window !== 'undefined' ? {
                user_agent: window.navigator.userAgent,
                platform: window.navigator.platform,
                language: window.navigator.language,
                screen: `${window.screen.width}x${window.screen.height}`,
                color_depth: window.screen.colorDepth,
                hardware_concurrency: window.navigator.hardwareConcurrency,
                device_memory: window.navigator.deviceMemory,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            } : {};

            const finalizeAndRedirect = async (payload = null, icon = 'warning') => {
                if (durationFinalized) {
                    return;
                }

                durationFinalized = true;
                locked = true;

                if (payload?.message) {
                    await Swal.fire({
                        title: icon === 'error' ? 'Ujian Dihentikan' : 'Sesi Berakhir',
                        text: payload.message,
                        icon,
                        confirmButtonText: 'OK',
                    });
                }

                if (payload?.redirect_to) {
                    window.location.href = payload.redirect_to;
                } else {
                    router.get('/student/dashboard');
                }
            };

            const persistDuration = async () => {
                if (!props.duration?.id || durationFinalized || isPersistingDuration) {
                    return !durationFinalized;
                }

                isPersistingDuration = true;

                try {
                    const { data } = await axios.put(`/student/exam-duration/update/${props.duration.id}`, {
                        duration: duration.value,
                        device_token: deviceToken.value,
                        device_info: deviceInfo,
                    });

                    if (typeof data?.remaining === 'number') {
                        duration.value = Math.min(duration.value, data.remaining);
                    }

                    return true;
                } catch (error) {
                    await finalizeAndRedirect(error?.response?.data);
                    return false;
                } finally {
                    isPersistingDuration = false;
                }
            };

            const handleChangeDuration = async () => {
                if (durationFinalized) {
                    return;
                }

                duration.value = Math.max(0, duration.value - 1000);
                counter.value = counter.value + 1;

                if (duration.value <= 0) {
                    await persistDuration();
                    return;
                }

                if (counter.value % 10 === 1) {
                    await persistDuration();
                }
            };

            const prevPage = async () => {
                if (durationFinalized || props.page <= 1) {
                    return;
                }

                const ok = await persistDuration();
                if (!ok) {
                    return;
                }

                router.get(`/student/exam/${props.id}/${props.page - 1}`);
            };

            const nextPage = async () => {
                if (durationFinalized) {
                    return;
                }

                const ok = await persistDuration();
                if (!ok) {
                    return;
                }

                router.get(`/student/exam/${props.id}/${props.page + 1}`);
            };

            const clickQuestion = async (index) => {
                if (durationFinalized) {
                    return;
                }

                const ok = await persistDuration();
                if (!ok) {
                    return;
                }

                router.get(`/student/exam/${props.id}/${index + 1}`);
            };

            const submitAnswer = async (exam_id, question_id, answer) => {
                if (durationFinalized) {
                    return;
                }

                const ok = await persistDuration();
                if (!ok) {
                    return;
                }

                router.post('/student/exam-answer', {
                    exam_id,
                    exam_session_id: props.exam_group.exam_session.id,
                    question_id,
                    answer,
                    device_token: deviceToken.value,
                    device_info: deviceInfo,
                }, {
                    preserveScroll: true,
                });
            };

            const endExam = async () => {
                if (durationFinalized) {
                    return;
                }

                const ok = await persistDuration();
                if (!ok) {
                    return;
                }

                router.post('/student/exam-end', {
                    exam_group_id: props.exam_group.id,
                    exam_id: props.exam_group.exam.id,
                    exam_session_id: props.exam_group.exam_session.id,
                    device_token: deviceToken.value,
                    device_info: deviceInfo,
                });
            };

            const cheatingThreshold = 3;
            let localCheatCount = 0;

            const reportCheat = async (type, meta = {}) => {
                if (locked || durationFinalized) {
                    return;
                }

                try {
                    const { data } = await axios.post('/student/exam-anti-cheat', {
                        exam_id: props.exam_group.exam.id,
                        exam_session_id: props.exam_group.exam_session.id,
                        grade_id: props.duration.id,
                        type,
                        meta: {
                            device_token: deviceToken.value,
                            device_info: deviceInfo,
                            ...meta,
                        },
                    });

                    if (data.success) {
                        if (data.locked) {
                            await finalizeAndRedirect(data, 'error');
                        } else {
                            localCheatCount = data.cheat_count ?? (localCheatCount + 1);
                            const remaining = data.remaining ?? Math.max(0, cheatingThreshold - localCheatCount);

                            Swal.fire({
                                title: 'Peringatan!',
                                text: `Terdeteksi kecurangan (${localCheatCount}). Kesempatan tersisa: ${remaining}.`,
                                icon: 'warning',
                                timer: 2500,
                                showConfirmButton: false,
                            });
                        }
                    }
                } catch (error) {
                    await finalizeAndRedirect(error?.response?.data);
                }
            };

            const handleVisibility = () => {
                if (document.hidden) {
                    reportCheat('VISIBILITY_HIDDEN');
                }
            };

            const handleBlur = () => {
                reportCheat('WINDOW_BLUR');
            };

            const handleContextMenu = (event) => {
                event.preventDefault();
                reportCheat('CONTEXT_MENU');
            };

            const handleKeydown = (event) => {
                const loweredKey = event.key.toLowerCase();

                if ((event.ctrlKey || event.metaKey) && ['c', 'x', 'v', 'a'].includes(loweredKey)) {
                    event.preventDefault();
                    reportCheat('KEYBOARD_SHORTCUT', { key: event.key });
                }

                if (event.key === 'PrintScreen') {
                    event.preventDefault();
                    reportCheat('PRINTSCREEN_KEY');
                }

                if (event.key === 'F12' || (event.ctrlKey && event.shiftKey && loweredKey === 'i')) {
                    event.preventDefault();
                    reportCheat('DEVTOOLS_SHORTCUT');
                }
            };

            let devtoolsOpen = false;
            const checkDevtools = () => {
                if (typeof window === 'undefined' || durationFinalized) {
                    return;
                }

                const widthThreshold = window.outerWidth - window.innerWidth > 160;
                const heightThreshold = window.outerHeight - window.innerHeight > 160;
                const opened = widthThreshold || heightThreshold;

                if (opened && !devtoolsOpen) {
                    devtoolsOpen = true;
                    reportCheat('DEVTOOLS_DETECTED');
                } else if (!opened && devtoolsOpen) {
                    devtoolsOpen = false;
                }
            };

            const requestFullscreenIfAvailable = async () => {
                if (durationFinalized) {
                    return;
                }

                const el = document.documentElement;
                if (el.requestFullscreen && !document.fullscreenElement) {
                    try {
                        await el.requestFullscreen();
                    } catch (_) {
                        // ignore failure
                    }
                }
            };

            const handleFullscreenChange = () => {
                const inFullscreen = Boolean(document.fullscreenElement);
                if (!inFullscreen) {
                    reportCheat('FULLSCREEN_EXIT');
                    requestFullscreenIfAvailable();
                }
            };

            const handleDeviceChange = async () => {
                if (typeof navigator === 'undefined' || !navigator.mediaDevices) {
                    return;
                }

                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const suspicious = devices
                        .filter((device) => device.kind === 'videoinput' && /screen|display|record|virtual/i.test(device.label || ''))
                        .map((device) => ({ id: device.deviceId, label: device.label }));

                    if (suspicious.length > 0) {
                        reportCheat('SCREEN_RECORDING_DEVICE', { devices: suspicious });
                    }
                } catch (_) {
                    // ignore enumeration errors
                }
            };

            const setupScreenMonitoring = () => {
                if (typeof navigator === 'undefined' || !navigator.mediaDevices) {
                    return;
                }

                if (navigator.mediaDevices.getDisplayMedia && !navigator.mediaDevices.__examPatched) {
                    originalGetDisplayMedia = navigator.mediaDevices.getDisplayMedia.bind(navigator.mediaDevices);
                    navigator.mediaDevices.getDisplayMedia = async (...args) => {
                        reportCheat('SCREEN_RECORDING_REQUEST');
                        return originalGetDisplayMedia(...args);
                    };
                    navigator.mediaDevices.__examPatched = true;
                }

                deviceChangeListener = handleDeviceChange;

                if (navigator.mediaDevices.addEventListener) {
                    navigator.mediaDevices.addEventListener('devicechange', deviceChangeListener);
                } else {
                    navigator.mediaDevices.ondevicechange = deviceChangeListener;
                }

                handleDeviceChange();
            };

            onMounted(async () => {
                if (typeof window === 'undefined') {
                    return;
                }

                document.addEventListener('visibilitychange', handleVisibility);
                window.addEventListener('blur', handleBlur);
                window.addEventListener('keydown', handleKeydown);
                window.addEventListener('resize', checkDevtools);
                window.addEventListener('focus', checkDevtools);
                document.addEventListener('contextmenu', handleContextMenu);
                document.addEventListener('fullscreenchange', handleFullscreenChange);

                requestFullscreenIfAvailable();
                checkDevtools();
                checkDevtoolsInterval = window.setInterval(checkDevtools, 1500);

                setupScreenMonitoring();
                await persistDuration();
            });

            onBeforeUnmount(() => {
                if (typeof window === 'undefined') {
                    return;
                }

                document.removeEventListener('visibilitychange', handleVisibility);
                window.removeEventListener('blur', handleBlur);
                window.removeEventListener('keydown', handleKeydown);
                window.removeEventListener('resize', checkDevtools);
                window.removeEventListener('focus', checkDevtools);
                document.removeEventListener('contextmenu', handleContextMenu);
                document.removeEventListener('fullscreenchange', handleFullscreenChange);

                if (checkDevtoolsInterval) {
                    clearInterval(checkDevtoolsInterval);
                    checkDevtoolsInterval = null;
                }

                if (document.exitFullscreen && document.fullscreenElement) {
                    document.exitFullscreen().catch(() => {});
                }

                if (navigator.mediaDevices) {
                    if (navigator.mediaDevices.addEventListener && deviceChangeListener) {
                        navigator.mediaDevices.removeEventListener('devicechange', deviceChangeListener);
                    } else if (navigator.mediaDevices.ondevicechange === deviceChangeListener) {
                        navigator.mediaDevices.ondevicechange = null;
                    }

                    if (navigator.mediaDevices.__examPatched && originalGetDisplayMedia) {
                        navigator.mediaDevices.getDisplayMedia = originalGetDisplayMedia;
                        delete navigator.mediaDevices.__examPatched;
                    }
                }
            });

            return {
                options,
                duration,
                handleChangeDuration,
                prevPage,
                nextPage,
                clickQuestion,
                submitAnswer,
                showModalEndExam,
                showModalEndTimeExam,
                endExam,
            };
        }
    }

</script>

<style>

</style>
/* Basic anti-cheat UX hardening */
* {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
img, svg { pointer-events: none; }

<template>
    <Head>
        <title>Ujian Dengan Nomor Soal : {{ page }} - Aplikasi Ujian Online</title>
    </Head>
    <div v-if="isOffline" class="alert alert-warning border-0 shadow mb-4">
        <i class="fa fa-wifi"></i> Koneksi terputus. Jawaban akan dikirim otomatis ketika koneksi kembali.
    </div>
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

                                        <button v-if="isOptionSelected(answer)" class="btn btn-info btn-sm w-100 shadow">{{ options[index] }}</button>

                                        <button v-else @click.prevent="selectAnswer(question_active.question.id, answer)" class="btn btn-outline-info btn-sm w-100 shadow">{{ options[index] }}</button>

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
                            <button v-if="page < totalQuestions" @click.prevent="nextPage" type="button" class="btn btn-gray-400 btn-sm">Selanjutnya</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow">
                <div class="card-header text-center">
                    <div class="badge bg-success p-2"> {{ answeredCount }} dikerjakan</div>
                </div>
                <div class="card-body" style="height: 330px;overflow-y: auto">

                    <div v-for="(question, index) in all_questions" :key="index">
                        <div width="20%" style="width: 20%; float: left;">
                            <div style="padding: 5px;">

                                <button @click.prevent="clickQuestion(index)" v-if="index+1 == page" class="btn btn-gray-400 btn-sm w-100">{{ index + 1 }}</button>

                                <button @click.prevent="clickQuestion(index)" v-if="index + 1 != page && !isQuestionAnswered(question)" class="btn btn-outline-info btn-sm w-100">{{ index + 1 }}</button>

                                <button @click.prevent="clickQuestion(index)" v-if="index + 1 != page && isQuestionAnswered(question)" class="btn btn-info btn-sm w-100">{{ index + 1 }}</button>
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

    import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue';

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
            let isFlushing = false;
            let autosaveInterval = null;

            const examId = props.exam_group.exam.id;
            const examSessionId = props.exam_group.exam_session.id;
            const gradeId = props.duration?.id;
            const storageKey = `exam_state_${gradeId}`;

            const selectedAnswers = reactive({});
            const pendingAnswers = reactive({});

            const isOffline = ref(false);

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

            const totalQuestions = computed(() => props.all_questions.length);
            const answeredCount = computed(() => Object.keys(selectedAnswers).filter((key) => Number(selectedAnswers[key] ?? 0) !== 0).length);

            const getQuestionKey = (questionId) => {
                if (questionId === null || questionId === undefined) {
                    return null;
                }

                return String(questionId);
            };

            const saveLocalState = () => {
                if (typeof window === 'undefined') {
                    return;
                }

                try {
                    const answersCopy = {};
                    Object.keys(selectedAnswers).forEach((key) => {
                        answersCopy[key] = selectedAnswers[key];
                    });

                    const pendingCopy = {};
                    Object.keys(pendingAnswers).forEach((key) => {
                        pendingCopy[key] = pendingAnswers[key];
                    });

                    window.localStorage.setItem(storageKey, JSON.stringify({
                        answers: answersCopy,
                        pending: pendingCopy,
                        duration: duration.value,
                        updated_at: Date.now(),
                    }));
                } catch (_) {
                    // ignore quota errors
                }
            };

            const clearLocalState = () => {
                if (typeof window === 'undefined') {
                    return;
                }

                try {
                    window.localStorage.removeItem(storageKey);
                } catch (_) {
                    // ignore
                }
            };

            const loadLocalState = () => {
                if (typeof window === 'undefined') {
                    return;
                }

                try {
                    const raw = window.localStorage.getItem(storageKey);
                    if (!raw) {
                        return;
                    }

                    const parsed = JSON.parse(raw);

                    if (parsed.answers) {
                        Object.entries(parsed.answers).forEach(([key, value]) => {
                            const numeric = Number(value);
                            if (!Number.isNaN(numeric)) {
                                selectedAnswers[key] = numeric;
                            }
                        });
                    }

                    if (parsed.pending) {
                        Object.entries(parsed.pending).forEach(([key, value]) => {
                            const numeric = Number(value);
                            if (!Number.isNaN(numeric)) {
                                pendingAnswers[key] = numeric;
                            }
                        });
                    }

                    if (parsed.duration && Number.isFinite(parsed.duration)) {
                        duration.value = Math.min(duration.value, Number(parsed.duration));
                    }

                    const serverAnswers = {};
                    (props.all_questions ?? []).forEach((record) => {
                        const key = getQuestionKey(record.question_id ?? record.question?.id);
                        if (!key) {
                            return;
                        }
                        serverAnswers[key] = Number(record.answer ?? 0);
                    });

                    Object.keys(selectedAnswers).forEach((key) => {
                        const selected = Number(selectedAnswers[key] ?? 0);
                        const serverValue = serverAnswers[key] ?? 0;
                        if (selected !== serverValue && selected !== 0) {
                            pendingAnswers[key] = selected;
                        }
                    });
                } catch (_) {
                    // ignore malformed storage
                }
            };

            const recordedAnswer = (record) => {
                if (!record) {
                    return 0;
                }
                const questionId = getQuestionKey(record.question_id ?? record.question?.id);
                if (!questionId) {
                    return Number(record.answer ?? 0);
                }
                if (selectedAnswers[questionId] !== undefined) {
                    return Number(selectedAnswers[questionId] ?? 0);
                }
                return Number(record.answer ?? 0);
            };

            const isOptionSelected = (option) => recordedAnswer(props.question_active) === option;
            const isQuestionAnswered = (record) => recordedAnswer(record) !== 0;

            const seedAnswersFromServer = (records = []) => {
                records.forEach((record) => {
                const questionId = getQuestionKey(record.question_id ?? record.question?.id);
                if (!questionId) {
                    return;
                }

                    const serverAnswer = Number(record.answer ?? 0);

                    if (pendingAnswers[questionId] !== undefined) {
                        return;
                    }

                    if (serverAnswer !== 0 || selectedAnswers[questionId] === undefined) {
                        selectedAnswers[questionId] = serverAnswer;
                    }
                });
            };

            watch(() => props.all_questions, (records) => {
                seedAnswersFromServer(records ?? []);
            }, { immediate: true });

            watch(() => props.question_active, (record) => {
                const questionId = getQuestionKey(record?.question?.id ?? record?.question_id);
                if (!questionId) {
                    return;
                }

                if (selectedAnswers[questionId] === undefined && record?.answer) {
                    selectedAnswers[questionId] = Number(record.answer);
                }
            });

            const finalizeAndRedirect = async (payload = null, icon = 'warning') => {
                if (durationFinalized) {
                    return;
                }

                durationFinalized = true;
                locked = true;

                clearLocalState();

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

                    isOffline.value = false;
                    saveLocalState();

                    return true;
                } catch (error) {
                    if (!error.response) {
                        isOffline.value = true;
                        saveLocalState();
                        return true;
                    }

                    await finalizeAndRedirect(error.response.data, 'error');
                    return false;
                } finally {
                    isPersistingDuration = false;
                }
            };

            const flushPendingAnswers = async () => {
                if (durationFinalized || isFlushing) {
                    return;
                }

                const keys = Object.keys(pendingAnswers);
                if (!keys.length) {
                    return;
                }

                if (!gradeId) {
                    return;
                }

                const payload = keys.map((key) => ({
                    question_id: Number(key),
                    answer: Number(pendingAnswers[key]),
                }));

                if (!payload.length) {
                    return;
                }

                isFlushing = true;

                try {
                    const { data } = await axios.post('/student/exam-auto-save', {
                        exam_id: examId,
                        exam_session_id: examSessionId,
                        grade_id: gradeId,
                        answers: payload,
                        device_token: deviceToken.value,
                        device_info: deviceInfo,
                    });

                    if (Array.isArray(data?.synced_answers)) {
                        data.synced_answers.forEach((questionId) => {
                            const key = getQuestionKey(questionId);
                            const sent = payload.find((entry) => entry.question_id === Number(questionId));
                            if (sent && pendingAnswers[key] === sent.answer) {
                                delete pendingAnswers[key];
                            }
                        });
                    }

                    isOffline.value = false;
                    saveLocalState();
                } catch (error) {
                    if (!error.response) {
                        isOffline.value = true;
                        saveLocalState();
                    } else if (error.response.status === 423) {
                        await finalizeAndRedirect(error.response.data, 'error');
                    }
                } finally {
                    isFlushing = false;
                }
            };

            const selectAnswer = (questionId, answerValue) => {
                if (durationFinalized) {
                    return;
                }

                const key = getQuestionKey(questionId);
                if (!key) {
                    return;
                }

                const numeric = Number(answerValue);
                selectedAnswers[key] = numeric;
                pendingAnswers[key] = numeric;

                saveLocalState();
                flushPendingAnswers();
            };

            const handleChangeDuration = async () => {
                if (durationFinalized) {
                    return;
                }

                duration.value = Math.max(0, duration.value - 1000);
                counter.value = counter.value + 1;

                 saveLocalState();

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

                saveLocalState();
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

                saveLocalState();
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

                saveLocalState();
                router.get(`/student/exam/${props.id}/${index + 1}`);
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
                }, {
                    onSuccess: () => {
                        clearLocalState();
                    },
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

            const handleOnline = () => {
                isOffline.value = false;
                flushPendingAnswers();
                persistDuration();
            };

            const handleOffline = () => {
                isOffline.value = true;
                saveLocalState();
            };

            onMounted(async () => {
                if (typeof window === 'undefined') {
                    return;
                }

                if (typeof navigator !== 'undefined' && navigator.onLine === false) {
                    isOffline.value = true;
                }

                seedAnswersFromServer(props.all_questions ?? []);
                loadLocalState();

                document.addEventListener('visibilitychange', handleVisibility);
                window.addEventListener('blur', handleBlur);
                window.addEventListener('keydown', handleKeydown);
                window.addEventListener('resize', checkDevtools);
                window.addEventListener('focus', checkDevtools);
                document.addEventListener('contextmenu', handleContextMenu);
                document.addEventListener('fullscreenchange', handleFullscreenChange);
                window.addEventListener('online', handleOnline);
                window.addEventListener('offline', handleOffline);
                window.addEventListener('beforeunload', saveLocalState);

                requestFullscreenIfAvailable();
                checkDevtools();
                checkDevtoolsInterval = window.setInterval(checkDevtools, 1500);

                setupScreenMonitoring();
                await persistDuration();
                await flushPendingAnswers();

                autosaveInterval = window.setInterval(() => {
                    flushPendingAnswers();
                    saveLocalState();
                }, 10000);
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
                window.removeEventListener('online', handleOnline);
                window.removeEventListener('offline', handleOffline);
                window.removeEventListener('beforeunload', saveLocalState);

                if (checkDevtoolsInterval) {
                    clearInterval(checkDevtoolsInterval);
                    checkDevtoolsInterval = null;
                }

                if (autosaveInterval) {
                    clearInterval(autosaveInterval);
                    autosaveInterval = null;
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

                saveLocalState();
            });

            return {
                options,
                duration,
                handleChangeDuration,
                prevPage,
                nextPage,
                clickQuestion,
                selectAnswer,
                showModalEndExam,
                showModalEndTimeExam,
                endExam,
                totalQuestions,
                answeredCount,
                isOptionSelected,
                isQuestionAnswered,
                isOffline,
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

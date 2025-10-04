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
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                        <h5 class="mb-0">Soal No. <strong class="fw-bold">{{ page }}</strong></h5>
                        <VueCountdown
                            :time="duration"
                            @progress="handleChangeDuration"
                            @end="showModalEndTimeExam = true"
                            v-slot="{ hours, minutes, seconds }"
                        >
                            <span class="badge bg-info p-2">
                                <i class="fa fa-clock"></i>
                                {{ hours }} jam, {{ minutes }} menit, {{ seconds }} detik.
                            </span>
                        </VueCountdown>
                    </div>
                </div>
                <div class="card-body">

                    <div v-if="question_active !== null">

                        <div>
                            <p v-html="question_active.question.question"></p>
                        </div>

                        <div class="exam-option-list">
                            <div
                                v-for="(answer, index) in answer_order"
                                :key="index"
                                class="exam-option-item"
                            >
                                <div class="exam-option-item__label">
                                    <button
                                        type="button"
                                        @click.prevent="selectAnswer(question_active.question.id, answer)"
                                        :class="[
                                            'btn btn-sm w-100 shadow exam-option-button',
                                            isOptionSelected(answer) ? 'btn-info active' : 'btn-outline-info'
                                        ]"
                                    >
                                        {{ options[index] }}
                                    </button>
                                </div>
                                <div class="exam-option-item__content">
                                    <p class="mb-0" v-html="question_active.question['option_'+answer]"></p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-else>
                        <div class="alert alert-danger border-0 shadow">
                            <i class="fa fa-exclamation-triangle"></i> Soal Tidak Ditemukan!.
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch gap-2">
                        <button
                            v-if="page > 1"
                            @click.prevent="prevPage"
                            type="button"
                            class="btn btn-gray-400 btn-sm"
                        >
                            Sebelumnya
                        </button>
                        <button
                            v-if="page < totalQuestions"
                            @click.prevent="nextPage"
                            type="button"
                            class="btn btn-gray-400 btn-sm"
                        >
                            Selanjutnya
                        </button>
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

                    <div class="question-grid">
                        <button
                            v-for="(question, index) in all_questions"
                            :key="index"
                            type="button"
                            @click.prevent="clickQuestion(index)"
                            :class="questionButtonClass(index, question)"
                        >
                            {{ index + 1 }}
                        </button>
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
            let serverSyncInterval = null;
            let canvasMonitorInterval = null;
            let screenFingerprintBaseline = null;
            let lastServerTick = null;
            let lastScreenshotEvent = 0;
            let lastServerRollbackReport = 0;

            const examId = props.exam_group.exam.id;
            const examSessionId = props.exam_group.exam_session.id;
            const gradeId = props.duration?.id;
            const storageKey = `exam_state_${gradeId}`;

            const selectedAnswers = reactive({});
            const pendingAnswers = reactive({});
            const totalTimePerQuestion = reactive({});

            const questionTimer = reactive({
                questionId: null,
                startedAt: null,
            });

            const isOffline = ref(false);
            const serverTimeOffset = ref(0);

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

            const getServerNow = () => Date.now() + serverTimeOffset.value;

            const computeScreenFingerprint = () => {
                if (typeof document === 'undefined') {
                    return null;
                }

                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = 220;
                    canvas.height = 60;
                    const ctx = canvas.getContext('2d');

                    if (!ctx) {
                        return null;
                    }

                    const metrics = [
                        window.screen.width,
                        window.screen.height,
                        window.screen.availWidth,
                        window.screen.availHeight,
                        window.devicePixelRatio,
                        window.outerWidth,
                        window.outerHeight,
                        window.visualViewport?.scale ?? 1,
                    ].join('|');

                    ctx.fillStyle = '#0f172a';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = '#38bdf8';
                    ctx.font = '14px monospace';
                    ctx.fillText(metrics, 6, 24);
                    ctx.strokeStyle = '#f97316';
                    ctx.strokeRect(1, 1, canvas.width - 2, canvas.height - 2);

                    return canvas.toDataURL();
                } catch (error) {
                    return null;
                }
            };

            const monitorCanvasFingerprint = () => {
                if (durationFinalized) {
                    return;
                }

                const fingerprint = computeScreenFingerprint();
                if (!fingerprint) {
                    return;
                }

                if (!screenFingerprintBaseline) {
                    screenFingerprintBaseline = fingerprint;
                    return;
                }

                if (fingerprint !== screenFingerprintBaseline) {
                    screenFingerprintBaseline = fingerprint;
                    reportCheat('SCREEN_FINGERPRINT_CHANGE', {
                        fingerprint,
                    });
                }
            };

            const totalQuestions = computed(() => props.all_questions.length);
            const answeredCount = computed(() => Object.keys(selectedAnswers).filter((key) => Number(selectedAnswers[key] ?? 0) !== 0).length);

            const getQuestionKey = (questionId) => {
                if (questionId === null || questionId === undefined) {
                    return null;
                }

                return String(questionId);
            };

            const ensurePendingEntry = (key) => {
                if (!key) {
                    return null;
                }

                if (!pendingAnswers[key]) {
                    pendingAnswers[key] = {
                        answer: Number(selectedAnswers[key] ?? 0),
                        time_spent_ms: 0,
                    };
                }

                return pendingAnswers[key];
            };

            const addTimeForQuestion = (questionId, delta) => {
                if (!questionId || !Number.isFinite(delta) || delta <= 0) {
                    return;
                }

                totalTimePerQuestion[questionId] = (totalTimePerQuestion[questionId] ?? 0) + delta;

                const entry = ensurePendingEntry(questionId);
                if (!entry) {
                    return;
                }

                entry.time_spent_ms = (entry.time_spent_ms ?? 0) + delta;
            };

            const commitCurrentQuestionTime = () => {
                const questionId = questionTimer.questionId;
                if (!questionId || questionTimer.startedAt === null) {
                    return;
                }

                const now = getServerNow();
                let delta = now - questionTimer.startedAt;
                questionTimer.startedAt = now;

                if (!Number.isFinite(delta) || delta <= 0) {
                    return;
                }

                addTimeForQuestion(questionId, delta);
            };

            const startQuestionTimer = (questionId) => {
                const key = getQuestionKey(questionId);
                if (!key) {
                    return;
                }

                if (questionTimer.questionId && questionTimer.questionId !== key) {
                    commitCurrentQuestionTime();
                }

                questionTimer.questionId = key;
                questionTimer.startedAt = getServerNow();
                ensurePendingEntry(key);
            };

            const saveLocalState = () => {
                commitCurrentQuestionTime();

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
                        const entry = pendingAnswers[key];
                        pendingCopy[key] = {
                            answer: Number(entry?.answer ?? selectedAnswers[key] ?? 0),
                            time_spent_ms: Math.max(0, Number(entry?.time_spent_ms ?? 0)),
                        };
                    });

                    const timeCopy = {};
                    Object.keys(totalTimePerQuestion).forEach((key) => {
                        timeCopy[key] = Math.max(0, Number(totalTimePerQuestion[key] ?? 0));
                    });

                    window.localStorage.setItem(storageKey, JSON.stringify({
                        answers: answersCopy,
                        pending: pendingCopy,
                        duration: duration.value,
                        question_times: timeCopy,
                        timer: {
                            questionId: questionTimer.questionId,
                            startedAt: questionTimer.startedAt,
                        },
                        updated_at: Date.now(),
                    }));
                } catch (_) {
                    // ignore quota errors
                }
            };

            const clearLocalState = () => {
                commitCurrentQuestionTime();

                if (typeof window === 'undefined') {
                    return;
                }

                try {
                    window.localStorage.removeItem(storageKey);
                } catch (_) {
                    // ignore
                }

                Object.keys(pendingAnswers).forEach((key) => {
                    delete pendingAnswers[key];
                });

                Object.keys(totalTimePerQuestion).forEach((key) => {
                    delete totalTimePerQuestion[key];
                });

                questionTimer.questionId = null;
                questionTimer.startedAt = null;
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
                            if (value && typeof value === 'object') {
                                pendingAnswers[key] = {
                                    answer: Number(value.answer ?? selectedAnswers[key] ?? 0),
                                    time_spent_ms: Math.max(0, Number(value.time_spent_ms ?? 0)),
                                };
                            } else {
                                pendingAnswers[key] = {
                                    answer: Number(value ?? selectedAnswers[key] ?? 0),
                                    time_spent_ms: 0,
                                };
                            }
                        });
                    }

                    if (parsed.question_times) {
                        Object.entries(parsed.question_times).forEach(([key, value]) => {
                            const numeric = Number(value);
                            if (Number.isFinite(numeric) && numeric >= 0) {
                                totalTimePerQuestion[key] = numeric;
                            }
                        });
                    }

                    if (parsed.timer && parsed.timer.questionId) {
                        questionTimer.questionId = String(parsed.timer.questionId);
                        questionTimer.startedAt = getServerNow();
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
                            const existing = pendingAnswers[key] ?? {};
                            pendingAnswers[key] = {
                                answer: selected,
                                time_spent_ms: Math.max(0, Number(existing.time_spent_ms ?? 0)),
                            };
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

            const questionButtonClass = (index, question) => {
                const classes = ['btn', 'btn-sm', 'w-100', 'question-grid__button', 'shadow-sm'];

                if (index + 1 === props.page) {
                    classes.push('btn-gray-400', 'active');
                } else if (isQuestionAnswered(question)) {
                    classes.push('btn-info');
                } else {
                    classes.push('btn-outline-info');
                }

                return classes.join(' ');
            };

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

                startQuestionTimer(questionId);
                ensurePendingEntry(questionId);

                if (selectedAnswers[questionId] === undefined && record?.answer) {
                    selectedAnswers[questionId] = Number(record.answer);
                }
            }, { immediate: true });

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

                commitCurrentQuestionTime();

                const keys = Object.keys(pendingAnswers);
                if (!keys.length || !gradeId) {
                    return;
                }

                const payload = keys.map((key) => {
                    const entry = pendingAnswers[key] ?? {};
                    return {
                        question_id: Number(key),
                        answer: Number(entry.answer ?? selectedAnswers[key] ?? 0),
                        time_spent_ms: Math.max(0, Math.round(Number(entry.time_spent_ms ?? 0))),
                    };
                }).filter((item) => Number.isFinite(item.question_id) && (!Number.isNaN(item.answer) || item.time_spent_ms > 0));

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

                    if (Array.isArray(data?.synced_answers) && data.synced_answers.length) {
                        data.synced_answers.forEach((questionId) => {
                            const key = getQuestionKey(questionId);
                            if (!key) {
                                return;
                            }
                            delete pendingAnswers[key];
                        });
                    } else {
                        keys.forEach((key) => {
                            delete pendingAnswers[key];
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

                commitCurrentQuestionTime();

                const numeric = Number(answerValue);
                selectedAnswers[key] = numeric;
                const entry = ensurePendingEntry(key) ?? {};
                entry.answer = numeric;
                entry.time_spent_ms = entry.time_spent_ms ?? 0;
                pendingAnswers[key] = {
                    answer: entry.answer,
                    time_spent_ms: entry.time_spent_ms,
                };

                saveLocalState();
                flushPendingAnswers();
            };

            const handleChangeDuration = async (payload) => {
                if (durationFinalized) {
                    return;
                }

                const serverNow = getServerNow();
                if (lastServerTick === null) {
                    lastServerTick = serverNow;
                }

                let delta = serverNow - lastServerTick;
                if (!Number.isFinite(delta) || delta <= 0) {
                    const clientNow = Date.now();
                    if (clientNow - lastServerRollbackReport > 10000) {
                        reportCheat('SERVER_TIME_ROLLBACK', {
                            delta,
                            client_timestamp: clientNow,
                        });
                        lastServerRollbackReport = clientNow;
                    }
                    delta = 1000;
                }

                delta = Math.min(delta, 5000);

                const countdownMs = Number(payload?.totalMilliseconds);
                let nextDuration = duration.value - delta;

                if (Number.isFinite(countdownMs)) {
                    nextDuration = Math.min(nextDuration, countdownMs);
                }

                duration.value = Math.max(0, nextDuration);
                counter.value = counter.value + 1;
                lastServerTick = serverNow;

                 saveLocalState();

                if (duration.value <= 0) {
                    await persistDuration();
                    return;
                }

                if (counter.value % 30 === 0) {
                    await syncServerTime(true);
                }

                if (counter.value % 10 === 1) {
                    await persistDuration();
                }
            };

            const prevPage = async () => {
                if (durationFinalized || props.page <= 1) {
                    return;
                }

                commitCurrentQuestionTime();
                await flushPendingAnswers();

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

                commitCurrentQuestionTime();
                await flushPendingAnswers();

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

                commitCurrentQuestionTime();
                await flushPendingAnswers();

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

                commitCurrentQuestionTime();
                await flushPendingAnswers();

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
            const serverDriftThreshold = 3000;
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

            const syncServerTime = async (report = false) => {
                try {
                    const { data } = await axios.get('/student/time-sync', {
                        headers: {
                            'Cache-Control': 'no-cache',
                        },
                    });

                    const timestamp = Number(data?.timestamp);
                    if (!Number.isFinite(timestamp)) {
                        return;
                    }

                    const now = Date.now();
                    const newOffset = timestamp - now;
                    const diff = Math.abs(newOffset - serverTimeOffset.value);

                    if (report && serverTimeOffset.value !== 0 && diff > serverDriftThreshold) {
                        reportCheat('SERVER_TIME_DRIFT', {
                            diff,
                            server_timestamp: timestamp,
                        });
                    }

                    serverTimeOffset.value = newOffset;
                    lastServerTick = timestamp;
                } catch (error) {
                    if (report && error?.response?.status === 401) {
                        await finalizeAndRedirect(error.response.data, 'error');
                    }
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

                if ((event.metaKey || event.ctrlKey) && event.shiftKey && ['3', '4', '5', 's'].includes(loweredKey)) {
                    event.preventDefault();
                    if (Date.now() - lastScreenshotEvent > 1000) {
                        reportCheat('SCREENSHOT_SHORTCUT', { combo: `${event.metaKey ? 'Meta' : 'Ctrl'}+Shift+${event.key}` });
                        lastScreenshotEvent = Date.now();
                    }
                }

                if (event.metaKey && ['3', '4'].includes(loweredKey) && !event.shiftKey) {
                    event.preventDefault();
                    if (Date.now() - lastScreenshotEvent > 1000) {
                        reportCheat('SCREENSHOT_SHORTCUT', { combo: `Meta+${event.key}` });
                        lastScreenshotEvent = Date.now();
                    }
                }

                if (event.key === 'PrintScreen') {
                    event.preventDefault();
                    reportCheat('PRINTSCREEN_KEY');
                    lastScreenshotEvent = Date.now();
                }

                if (event.key === 'F12' || (event.ctrlKey && event.shiftKey && loweredKey === 'i')) {
                    event.preventDefault();
                    reportCheat('DEVTOOLS_SHORTCUT');
                }
            };

            const handleKeyup = (event) => {
                const loweredKey = event.key.toLowerCase();

                if (event.key === 'PrintScreen' && Date.now() - lastScreenshotEvent > 800) {
                    reportCheat('PRINTSCREEN_KEY');
                    lastScreenshotEvent = Date.now();
                }

                if ((event.metaKey || event.ctrlKey) && event.shiftKey && ['3', '4', '5', 's'].includes(loweredKey)) {
                    if (Date.now() - lastScreenshotEvent > 800) {
                        reportCheat('SCREENSHOT_SHORTCUT', { combo: `${event.metaKey ? 'Meta' : 'Ctrl'}+Shift+${event.key}` });
                        lastScreenshotEvent = Date.now();
                    }
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
                syncServerTime();
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
                window.addEventListener('keyup', handleKeyup);
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
                await syncServerTime();
                lastServerTick = getServerNow();
                serverSyncInterval = window.setInterval(() => {
                    syncServerTime(true);
                }, 60000);
                monitorCanvasFingerprint();
                canvasMonitorInterval = window.setInterval(monitorCanvasFingerprint, 7000);
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

                commitCurrentQuestionTime();
                flushPendingAnswers();

                document.removeEventListener('visibilitychange', handleVisibility);
                window.removeEventListener('blur', handleBlur);
                window.removeEventListener('keydown', handleKeydown);
                window.removeEventListener('keyup', handleKeyup);
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

                if (serverSyncInterval) {
                    clearInterval(serverSyncInterval);
                    serverSyncInterval = null;
                }

                if (canvasMonitorInterval) {
                    clearInterval(canvasMonitorInterval);
                    canvasMonitorInterval = null;
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
                questionButtonClass,
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

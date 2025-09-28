<template>
  <Head>
    <title>Monitoring Kecurangan - Admin</title>
  </Head>
  <div class="container-fluid mb-5 mt-5">
    <div class="row mb-4">
      <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h6 class="text-muted mb-1">Total Pelanggaran</h6>
            <h3 class="fw-bold text-danger mb-0">{{ summary.total_events }}</h3>
            <small class="text-muted">Hari ini: {{ summary.today_events }}</small>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h6 class="text-muted mb-1">Akun Terkunci</h6>
            <h3 class="fw-bold text-warning mb-0">{{ summary.locked_students }}</h3>
            <small class="text-muted">Percobaan dikunci: {{ summary.locked_attempts }}</small>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h6 class="text-muted mb-1">Peringatan Aktif</h6>
            <h3 class="fw-bold text-info mb-0">{{ summary.warned_attempts }}</h3>
            <small class="text-muted">Menunggu tindak lanjut</small>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h6 class="text-muted mb-1">Top Pelanggaran</h6>
            <ul class="list-unstyled mb-0 small">
              <li v-if="type_breakdown.length === 0" class="text-muted">Belum ada data</li>
              <li v-for="item in type_breakdown" :key="item.type">
                <strong>{{ item.type }}</strong> — {{ item.total }}x
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12 col-xl-8 mb-4">
        <div class="card border-0 shadow">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Log Kecurangan</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Waktu</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Ujian</th>
                    <th>Sesi</th>
                    <th>Jenis</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="event in events.data" :key="event.id">
                    <td>{{ event.created_at }}</td>
                    <td>{{ event.student?.name }}</td>
                    <td>{{ event.student?.classroom?.title }}</td>
                    <td>{{ event.exam?.title }}</td>
                    <td>{{ event.exam_session?.title }}</td>
                    <td>{{ event.type }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-3">
              <Link class="btn btn-sm btn-outline-secondary me-2" :href="events.prev_page_url" v-if="events.prev_page_url">Prev</Link>
              <Link class="btn btn-sm btn-outline-secondary" :href="events.next_page_url" v-if="events.next_page_url">Next</Link>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4 mb-4">
        <div class="card border-0 shadow">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Akun Terkunci</h5>
          </div>
          <div class="card-body">
            <div v-if="locked_students.data.length === 0" class="alert alert-info border-0">Belum ada akun terkunci.</div>
            <div v-else>
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>Kelas</th>
                      <th>Terkunci Pada</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="s in locked_students.data" :key="s.id">
                      <td>{{ s.name }}</td>
                      <td>{{ s.classroom?.title }}</td>
                      <td>{{ s.locked_at }}</td>
                      <td>
                        <button @click="unlock(s.id)" class="btn btn-sm btn-success">Buka Kunci</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="mt-3">
                <Link class="btn btn-sm btn-outline-secondary me-2" :href="locked_students.prev_page_url" v-if="locked_students.prev_page_url">Prev</Link>
                <Link class="btn btn-sm btn-outline-secondary" :href="locked_students.next_page_url" v-if="locked_students.next_page_url">Next</Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import LayoutAdmin from '../../../Layouts/Admin.vue';
import { Head, Link, router } from '@inertiajs/vue3';

export default {
  layout: LayoutAdmin,
  components: { Head, Link },
  props: {
    events: Object,
    locked_students: Object,
    summary: Object,
    type_breakdown: Array,
  },
  setup() {
    const unlock = (id) => {
      router.post(`/admin/cheat-monitor/unlock/${id}`);
    };
    return { unlock };
  },
};
</script>

<style>
</style>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold mb-2">Jumlah Laporan per Bulan</h3>
        <canvas id="laporanChart"></canvas>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold mb-2">Status Laporan</h3>
        <canvas id="statusChart"></canvas>
    </div>
    <div class="bg-white rounded-lg shadow p-4 col-span-1 md:col-span-2">
        <h3 class="font-bold mb-2">Total Pendapatan per Bulan</h3>
        <canvas id="pendapatanChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data dari controller
    const labels = @json($labels);
    const laporanData = @json($laporanData);
    const pendapatanData = @json($pendapatanData);
    const statusLabels = @json(array_keys($statusData));
    const statusValues = @json(array_values($statusData));

    // Chart Jumlah Laporan per Bulan
    new Chart(document.getElementById('laporanChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Laporan',
                data: laporanData,
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    // Chart Status Laporan
    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                label: 'Status',
                data: statusValues,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(239, 68, 68, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Chart Total Pendapatan per Bulan
    new Chart(document.getElementById('pendapatanChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Pendapatan',
                data: pendapatanData,
                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script> 
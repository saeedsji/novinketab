import Chart from 'chart.js/auto';

// آبجکتی برای نگهداری نمونه‌های (instances) نمودارها
// این کار از ساخته شدن چندباره نمودارها جلوگیری می‌کند
let chartInstances = {};

// Listener اصلی که منتظر رویداد از سمت Livewire می‌ماند
document.addEventListener('livewire:initialized', () => {
    Livewire.on('updateAllCharts', ({ chartsData }) => {
        if (!chartsData) return;

        // لیستی از تمام نمودارهایی که می‌خواهیم بسازیم یا آپدیت کنیم
        const chartsToProcess = [
            { id: 'salesOverTimeChart', type: 'line', data: chartsData.salesOverTime, options: { responsive: true }, label: 'میزان فروش' },
            { id: 'salesByPlatformChart', type: 'doughnut', data: chartsData.salesByPlatform, options: { responsive: true, maintainAspectRatio: false } },
            { id: 'topBooksByRevenueChart', type: 'bar', data: chartsData.topBooksByRevenue, options: { indexAxis: 'y', responsive: true }, label: 'مبلغ فروش' },
            { id: 'topBooksBySalesChart', type: 'bar', data: chartsData.topBooksBySales, options: { indexAxis: 'y', responsive: true }, label: 'تعداد فروش' },
            { id: 'salesByCategoryChart', type: 'bar', data: chartsData.salesByCategory, options: { responsive: true }, label: 'فروش' },
            { id: 'salesByGenderChart', type: 'pie', data: chartsData.salesByGender, options: { responsive: true, maintainAspectRatio: false } }
        ];

        // تعریف رنگ‌ها
        const chartColors = [
            'rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(239, 68, 68, 0.8)',
            'rgba(245, 158, 11, 0.8)', 'rgba(99, 102, 241, 0.8)', 'rgba(139, 92, 246, 0.8)'
        ];

        chartsToProcess.forEach(chartConfig => {
            const canvas = document.getElementById(chartConfig.id);
            if (!canvas) return; // اگر المان canvas در صفحه نبود، ادامه نده

            const chartData = {
                labels: chartConfig.data.labels,
                datasets: [{
                    label: chartConfig.label || '',
                    data: chartConfig.data.values,
                    // برای نمودارهای دایره‌ای و دونات، از چندین رنگ استفاده کن
                    backgroundColor: ['pie', 'doughnut'].includes(chartConfig.type) ? chartColors : chartColors[0],
                }]
            };

            // اگر نمودار از قبل ساخته شده، فقط آن را آپدیت کن
            if (chartInstances[chartConfig.id]) {
                chartInstances[chartConfig.id].data = chartData;
                chartInstances[chartConfig.id].update();
            }
            // در غیر این صورت، یک نمودار جدید بساز
            else {
                chartInstances[chartConfig.id] = new Chart(canvas, {
                    type: chartConfig.type,
                    data: chartData,
                    options: chartConfig.options
                });
            }
        });
    });
});

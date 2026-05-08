class DashboardStats {
    constructor() {
        this.charts = {};
        this.currentPeriod = this.getDefaultPeriod();
        this.chartTheme = this.getChartTheme();
        this.init();
    }
    
    getDefaultPeriod() {
        const periodSelect = document.querySelector('[name="settings[stats_period]"]');
        if (periodSelect && periodSelect.value) {
            return periodSelect.value;
        }
        return 'month';
    }
    
    getChartTheme() {
        const themeSelect = document.querySelector('[name="settings[chart_theme]"]');
        if (themeSelect && themeSelect.value) {
            return themeSelect.value;
        }
        return 'modern';
    }
    
    getChartColors() {
        const themes = {
            modern: {
                publications: 'rgba(13, 110, 253, 0.7)',
                publicationsBorder: 'rgb(13, 110, 253)',
                comments: 'rgba(25, 135, 84, 0.7)',
                commentsBorder: 'rgb(25, 135, 84)',
                popular: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'],
                liked: ['#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0', '#0d6efd', '#6610f2', '#6f42c1', '#d63384']
            },
            pastel: {
                publications: 'rgba(54, 162, 235, 0.6)',
                publicationsBorder: 'rgb(54, 162, 235)',
                comments: 'rgba(75, 192, 192, 0.6)',
                commentsBorder: 'rgb(75, 192, 192)',
                popular: ['#ff9f4a', '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7', '#dfe6e9', '#a29bfe', '#fd79a8', '#fdcb6e'],
                liked: ['#ff6b6b', '#ff9f4a', '#fdcb6e', '#ffeaa7', '#4ecdc4', '#45b7d1', '#a29bfe', '#dfe6e9', '#fd79a8', '#96ceb4']
            },
            dark: {
                publications: 'rgba(100, 200, 255, 0.7)',
                publicationsBorder: 'rgb(100, 200, 255)',
                comments: 'rgba(80, 250, 150, 0.7)',
                commentsBorder: 'rgb(80, 250, 150)',
                popular: ['#64c8ff', '#b794f4', '#fbbf24', '#f87171', '#4ade80', '#2dd4bf', '#f472b6', '#a78bfa', '#fb923c', '#34d399'],
                liked: ['#f87171', '#fb923c', '#fbbf24', '#4ade80', '#2dd4bf', '#60a5fa', '#a78bfa', '#f472b6', '#c084fc', '#94a3b8']
            },
            corporate: {
                publications: 'rgba(0, 82, 147, 0.7)',
                publicationsBorder: 'rgb(0, 82, 147)',
                comments: 'rgba(0, 148, 68, 0.7)',
                commentsBorder: 'rgb(0, 148, 68)',
                popular: ['#005293', '#009444', '#d0a800', '#b31920', '#5b2e8e', '#e37200', '#00a0b0', '#e85d04', '#2196f3', '#4caf50'],
                liked: ['#b31920', '#e37200', '#d0a800', '#009444', '#005293', '#00a0b0', '#5b2e8e', '#e85d04', '#2196f3', '#4caf50']
            }
        };
        return themes[this.chartTheme] || themes.modern;
    }
    
    async init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.load());
        } else {
            await this.load();
        }
    }
    
    async load() {
        const showStats = document.querySelector('[name="settings[show_detailed_stats]"]');
        if (showStats && !showStats.checked) {
            return;
        }
        
        if (typeof Chart === 'undefined') {
            await this.waitForChart();
        }
        
        this.createStatsContainer();
        await this.loadAllStats();
        this.addPeriodListeners();
        
        setInterval(() => this.refresh(), 300000);
    }
    
    waitForChart() {
        return new Promise((resolve) => {
            if (typeof Chart !== 'undefined') {
                resolve();
            } else {
                const checkInterval = setInterval(() => {
                    if (typeof Chart !== 'undefined') {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 100);
            }
        });
    }
    
    createStatsContainer() {
        const statsGrid = document.querySelector('.stats-grid');
        if (!statsGrid) return;
        
        if (document.querySelector('.dashboard-stats-container')) return;
        
        const showPublications = this.getSettingValue('show_publications_chart', true);
        const showPopular = this.getSettingValue('show_popular_posts_chart', true);
        const showLiked = this.getSettingValue('show_liked_posts_chart', true);
        const showComments = this.getSettingValue('show_comments_chart', true);
        
        const statsContainer = document.createElement('div');
        statsContainer.className = 'dashboard-stats-container mt-4';
        statsContainer.innerHTML = `
            <div class="stats-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        ${lang === 'ru' ? 'Детальная статистика' : 'Detailed statistics'}
                    </h5>
                    <p class="text-muted small mb-0">${lang === 'ru' ? 'Аналитика публикаций и активности' : 'Publication and activity analytics'}</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm me-2">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i>${lang === 'ru' ? 'Экспорт' : 'Export'}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="exportFullHtml">
                                <i class="bi bi-file-earmark-text me-2"></i>${lang === 'ru' ? 'HTML (полный отчет)' : 'HTML (full report)'}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="exportPublicationsHtml">
                                <i class="bi bi-file-earmark-text me-2"></i>${lang === 'ru' ? 'HTML (публикации)' : 'HTML (publications)'}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="exportPopularHtml">
                                <i class="bi bi-file-earmark-text me-2"></i>${lang === 'ru' ? 'HTML (популярные)' : 'HTML (popular)'}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="exportCommentsHtml">
                                <i class="bi bi-file-earmark-text me-2"></i>${lang === 'ru' ? 'HTML (комментарии)' : 'HTML (comments)'}
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="exportPdf">
                                <i class="bi bi-file-pdf me-2"></i>${lang === 'ru' ? 'PDF (печать)' : 'PDF (print)'}
                            </a></li>
                        </ul>
                    </div>
                    <div class="period-selector btn-group btn-group-sm">
                        <button type="button" class="btn ${this.currentPeriod === 'week' ? 'btn-primary' : 'btn-outline-secondary'} period-btn" data-period="week">${lang === 'ru' ? 'Неделя' : 'Week'}</button>
                        <button type="button" class="btn ${this.currentPeriod === 'month' ? 'btn-primary' : 'btn-outline-secondary'} period-btn" data-period="month">${lang === 'ru' ? 'Месяц' : 'Month'}</button>
                        <button type="button" class="btn ${this.currentPeriod === 'quarter' ? 'btn-primary' : 'btn-outline-secondary'} period-btn" data-period="quarter">${lang === 'ru' ? 'Квартал' : 'Quarter'}</button>
                        <button type="button" class="btn ${this.currentPeriod === 'year' ? 'btn-primary' : 'btn-outline-secondary'} period-btn" data-period="year">${lang === 'ru' ? 'Год' : 'Year'}</button>
                        <button type="button" class="btn ${this.currentPeriod === 'all' ? 'btn-primary' : 'btn-outline-secondary'} period-btn" data-period="all">${lang === 'ru' ? 'Всё время' : 'All time'}</button>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshStatsBtn" title="${lang === 'ru' ? 'Обновить' : 'Refresh'}">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
            </div>
            
            <div class="row" id="stats-charts-container">
                ${showPublications ? `
                <div class="col-lg-8 mb-4" id="publications-chart-container">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-calendar me-2"></i>
                                ${lang === 'ru' ? 'Динамика публикаций' : 'Publication trends'}
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="publicationsChart" height="150"></canvas>
                            <div class="publications-stats mt-3 d-flex justify-content-around text-center">
                                <div>
                                    <div class="small text-muted">${lang === 'ru' ? 'Всего постов' : 'Total posts'}</div>
                                    <div class="h5 mb-0" id="total-posts">0</div>
                                </div>
                                <div>
                                    <div class="small text-muted">${lang === 'ru' ? 'За этот месяц' : 'This month'}</div>
                                    <div class="h5 mb-0" id="this-month-posts">0</div>
                                </div>
                                <div>
                                    <div class="small text-muted">${lang === 'ru' ? 'Динамика' : 'Trend'}</div>
                                    <div class="h5 mb-0" id="posts-trend">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                <div class="col-lg-4 mb-4" id="summary-stats-container">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                ${lang === 'ru' ? 'Общая статистика' : 'Overall statistics'}
                            </h6>
                        </div>
                        <div class="card-body" id="summary-stats-content">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted small">${lang === 'ru' ? 'Загрузка...' : 'Loading...'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${showPopular ? `
                <div class="col-lg-6 mb-4" id="popular-chart-container">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-eye me-2"></i>
                                ${lang === 'ru' ? 'Топ популярных постов' : 'Top popular posts'}
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="popularPostsChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                ${showLiked ? `
                <div class="col-lg-6 mb-4" id="liked-chart-container">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-heart-fill me-2 text-danger"></i>
                                ${lang === 'ru' ? 'Топ залайканных постов' : 'Top liked posts'}
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="likedPostsChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                ${showComments ? `
                <div class="col-lg-12 mb-4" id="comments-chart-container">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-chat-dots me-2"></i>
                                ${lang === 'ru' ? 'Статистика комментариев' : 'Comment statistics'}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    <canvas id="commentsChart" height="160"></canvas>
                                </div>
                                <div class="col-lg-4">
                                    <div id="comments-status-stats"></div>
                                    <div id="top-commented-posts"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        statsGrid.insertAdjacentElement('afterend', statsContainer);
        
        const exportFullHtml = document.getElementById('exportFullHtml');
        const exportPublicationsHtml = document.getElementById('exportPublicationsHtml');
        const exportPopularHtml = document.getElementById('exportPopularHtml');
        const exportCommentsHtml = document.getElementById('exportCommentsHtml');
        const exportPdf = document.getElementById('exportPdf');
        const refreshBtn = document.getElementById('refreshStatsBtn');
        
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refresh());
        }
        
        if (exportFullHtml) {
            exportFullHtml.addEventListener('click', (e) => {
                e.preventDefault();
                window.open(`${window.ADMIN_URL || '/admin'}/stats/export-html?type=full&period=${this.currentPeriod}`, '_blank');
            });
        }
        
        if (exportPublicationsHtml) {
            exportPublicationsHtml.addEventListener('click', (e) => {
                e.preventDefault();
                window.open(`${window.ADMIN_URL || '/admin'}/stats/export-html?type=publications&period=${this.currentPeriod}`, '_blank');
            });
        }
        
        if (exportPopularHtml) {
            exportPopularHtml.addEventListener('click', (e) => {
                e.preventDefault();
                window.open(`${window.ADMIN_URL || '/admin'}/stats/export-html?type=popular&period=${this.currentPeriod}`, '_blank');
            });
        }
        
        if (exportCommentsHtml) {
            exportCommentsHtml.addEventListener('click', (e) => {
                e.preventDefault();
                window.open(`${window.ADMIN_URL || '/admin'}/stats/export-html?type=comments&period=${this.currentPeriod}`, '_blank');
            });
        }
        
        if (exportPdf) {
            exportPdf.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportToPDF();
            });
        }
    }

    getSettingValue(settingName, defaultValue) {
        const input = document.querySelector(`[name="settings[${settingName}]"]`);
        if (input && input.type === 'checkbox') {
            return input.checked;
        }
        if (input && input.value) {
            return input.value === '1' || input.value === 'true';
        }
        return defaultValue;
    }
    
    async loadAllStats() {
        await Promise.all([
            this.loadPublicationsChart(),
            this.loadPopularPostsChart(),
            this.loadLikedPostsChart(),
            this.loadCommentsChart(),
            this.loadSummaryStats()
        ]);
    }
    
    async fetchStats(type, params = {}) {
        const url = new URL(`${window.ADMIN_URL || '/admin'}/stats/data`);
        url.searchParams.append('type', type);
        url.searchParams.append('period', this.currentPeriod);
        
        if (params.limit) {
            url.searchParams.append('limit', params.limit);
        }
        
        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const text = await response.text();
            
            if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<')) {
                return { error: lang === 'ru' ? 'Сервер вернул HTML вместо JSON' : 'Server returned HTML instead of JSON' };
            }
            
            const data = JSON.parse(text);
            return data;
            
        } catch (error) {
            console.error(`Error fetching ${type}:`, error);
            return { error: error.message };
        }
    }
    
    async loadPublicationsChart() {
        const data = await this.fetchStats('publications');
        
        if (data.error) {
            this.showChartError('publicationsChart', data.error);
            return;
        }
        
        const colors = this.getChartColors();
        const ctx = document.getElementById('publicationsChart');
        if (!ctx) return;
        
        if (this.charts.publications) {
            this.charts.publications.destroy();
        }
        
        this.charts.publications = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: lang === 'ru' ? 'Публикации' : 'Publications',
                    data: data.data,
                    borderColor: colors.publicationsBorder,
                    backgroundColor: colors.publications,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: colors.publicationsBorder,
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return `${lang === 'ru' ? 'Публикаций: ' : 'Publications: '}${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                }
            }
        });
        
        const totalEl = document.getElementById('total-posts');
        const thisMonthEl = document.getElementById('this-month-posts');
        if (totalEl) totalEl.textContent = data.total || 0;
        if (thisMonthEl) thisMonthEl.textContent = data.this_month || 0;
        
        const trendElement = document.getElementById('posts-trend');
        if (trendElement) {
            const trend = data.trend || 0;
            if (trend > 0) {
                trendElement.innerHTML = `<span class="trend-up text-success">▲ +${trend}%</span>`;
            } else if (trend < 0) {
                trendElement.innerHTML = `<span class="trend-down text-danger">▼ ${trend}%</span>`;
            } else {
                trendElement.innerHTML = `<span class="trend-zero text-muted">→ 0%</span>`;
            }
        }
    }
    
    async loadPopularPostsChart() {
        const limit = this.getTopPostsLimit();
        const data = await this.fetchStats('popular', { limit });
        
        if (data.error || !data.labels || data.labels.length === 0) {
            this.showChartError('popularPostsChart', data.error || (lang === 'ru' ? 'Нет данных' : 'No data'));
            return;
        }
        
        const colors = this.getChartColors();
        const ctx = document.getElementById('popularPostsChart');
        if (!ctx) return;
        
        if (this.charts.popular) {
            this.charts.popular.destroy();
        }
        
        this.charts.popular = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: lang === 'ru' ? 'Просмотры' : 'Views',
                    data: data.data,
                    backgroundColor: colors.popular,
                    borderRadius: 8,
                    barPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${lang === 'ru' ? 'Просмотров: ' : 'Views: '}${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: lang === 'ru' ? 'Количество просмотров' : 'views' }
                    }
                },
                onClick: (event, activeElements) => {
                    if (activeElements.length > 0 && data.posts) {
                        const index = activeElements[0].index;
                        const post = data.posts[index];
                        if (post && post.slug) {
                            window.open(`${window.BASE_URL || ''}/post/${post.slug}`, '_blank');
                        }
                    }
                }
            }
        });
    }
    
    async loadLikedPostsChart() {
        const limit = this.getTopPostsLimit();
        const data = await this.fetchStats('liked', { limit });
        
        if (data.error || !data.labels || data.labels.length === 0) {
            this.showChartError('likedPostsChart', data.error || (lang === 'ru' ? 'Нет данных' : 'No data'));
            return;
        }
        
        const colors = this.getChartColors();
        const ctx = document.getElementById('likedPostsChart');
        if (!ctx) return;
        
        if (this.charts.liked) {
            this.charts.liked.destroy();
        }
        
        this.charts.liked = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: lang === 'ru' ? 'Лайки' : 'Likes',
                    data: data.data,
                    backgroundColor: colors.liked,
                    borderRadius: 8,
                    barPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${lang === 'ru' ? 'Лайков: ' : 'Likes: '}${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: lang === 'ru' ? 'Количество лайков' : 'Number of likes' }
                    }
                },
                onClick: (event, activeElements) => {
                    if (activeElements.length > 0 && data.posts) {
                        const index = activeElements[0].index;
                        const post = data.posts[index];
                        if (post && post.slug) {
                            window.open(`${window.BASE_URL || ''}/post/${post.slug}`, '_blank');
                        }
                    }
                }
            }
        });
    }
    
    async loadCommentsChart() {
        const data = await this.fetchStats('comments');
        
        if (data.error) {
            this.showChartError('commentsChart', data.error);
            return;
        }
        
        const colors = this.getChartColors();
        const ctx = document.getElementById('commentsChart');
        if (!ctx) return;
        
        if (this.charts.comments) {
            this.charts.comments.destroy();
        }
        
        this.charts.comments = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: lang === 'ru' ? 'Комментарии' : 'Comments',
                    data: data.data,
                    borderColor: colors.commentsBorder,
                    backgroundColor: colors.comments,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: colors.commentsBorder,
                    pointBorderColor: '#fff',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${lang === 'ru' ? 'Комментариев: ' : 'Comments: '}${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                }
            }
        });
        
        const total = (data.status.approved + data.status.pending + data.status.spam) || 1;
        const statusHtml = `
            <div class="mb-4">
                <h6 class="small text-muted mb-2">${lang === 'ru' ? 'Статусы комментариев' : 'Comment statuses'}</h6>
                <div class="d-flex justify-content-between mb-1">
                    <span>${lang === 'ru' ? '✅ Одобрены' : '✅ Approved'}</span>
                    <span class="fw-bold">${data.status.approved}</span>
                </div>
                <div class="progress mb-2" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: ${(data.status.approved / total * 100).toFixed(1)}%"></div>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>${lang === 'ru' ? '⏳ На модерации' : '⏳ Pending moderation'}</span>
                    <span class="fw-bold">${data.status.pending}</span>
                </div>
                <div class="progress mb-2" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: ${(data.status.pending / total * 100).toFixed(1)}%"></div>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>${lang === 'ru' ? '⚠️ Спам' : '⚠️ Spam'}</span>
                    <span class="fw-bold">${data.status.spam}</span>
                </div>
                <div class="progress mb-2" style="height: 4px;">
                    <div class="progress-bar bg-danger" style="width: ${(data.status.spam / total * 100).toFixed(1)}%"></div>
                </div>
            </div>
        `;
        
        let topPostsHtml = '<h6 class="small text-muted mb-2">' + (lang === 'ru' ? 'Топ комментируемых постов' : 'Top commented posts') + '</h6>';
        if (data.top_posts && data.top_posts.length > 0) {
            topPostsHtml += '<div class="list-group list-group-flush">';
            data.top_posts.forEach(post => {
                topPostsHtml += `
                    <a href="${window.BASE_URL || ''}/post/${post.slug}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                        <span class="small text-truncate">${this.escapeHtml(post.title)}</span>
                        <span class="badge bg-primary rounded-pill">${post.comments_count}</span>
                    </a>
                `;
            });
            topPostsHtml += '</div>';
        } else {
            topPostsHtml += '<p class="text-muted small">' + (lang === 'ru' ? 'Нет данных' : 'No data') + '</p>';
        }
        
        const statusStatsDiv = document.getElementById('comments-status-stats');
        const topPostsDiv = document.getElementById('top-commented-posts');
        if (statusStatsDiv) statusStatsDiv.innerHTML = statusHtml;
        if (topPostsDiv) topPostsDiv.innerHTML = topPostsHtml;
    }
    
    async loadSummaryStats() {
        const data = await this.fetchStats('summary');
        
        if (data.error) {
            document.getElementById('summary-stats-content').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <p class="mt-2">${this.escapeHtml(data.error)}</p>
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="mb-4">
                <h6 class="small text-muted mb-2">${lang === 'ru' ? 'Активные комментаторы (30 дней)' : 'Active commenters (30 days)'}</h6>
                <div class="h3 mb-0">${data.active_commenters || 0}</div>
                <small class="text-muted">${lang === 'ru' ? 'пользователей оставили комментарии' : 'users left comments'}</small>
            </div>
            
            <div class="mb-4">
                <h6 class="small text-muted mb-2">${lang === 'ru' ? 'Посты по категориям' : 'Posts by categories'}</h6>
                <div class="categories-stats">
        `;
        
        if (data.categories && data.categories.length > 0) {
            const totalPosts = data.categories.reduce((sum, cat) => sum + (cat.posts_count || 0), 0) || 1;
            data.categories.forEach(cat => {
                const percent = ((cat.posts_count || 0) / totalPosts * 100).toFixed(1);
                html += `
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">${this.escapeHtml(cat.name)}</span>
                        <span class="small fw-bold">${cat.posts_count || 0}</span>
                    </div>
                    <div class="progress mb-2" style="height: 3px;">
                        <div class="progress-bar bg-info" style="width: ${percent}%"></div>
                    </div>
                `;
            });
        } else {
            html += '<p class="text-muted small">' + (lang === 'ru' ? 'Нет данных' : 'No data') + '</p>';
        }
        
        html += `
                </div>
            </div>
            
            <div>
                <h6 class="small text-muted mb-2">${lang === 'ru' ? 'Последние 12 месяцев' : 'Last 12 months'}</h6>
                <div id="mini-monthly-chart"></div>
            </div>
        `;
        
        const summaryContent = document.getElementById('summary-stats-content');
        if (summaryContent) summaryContent.innerHTML = html;
        
        if (data.monthly && data.monthly.length > 0) {
            this.createMiniChart(data.monthly);
        }
    }
    
    createMiniChart(monthlyData) {
        const container = document.getElementById('mini-monthly-chart');
        if (!container) return;
        
        container.innerHTML = '';
        const canvas = document.createElement('canvas');
        container.appendChild(canvas);
        
        const labels = monthlyData.map(m => {
            const date = new Date(m.month + '-01');
            return date.toLocaleString('ru', { month: 'short' });
        });
        const values = monthlyData.map(m => m.count);
        
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: 'rgba(13, 110, 253, 0.5)',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${lang === 'ru' ? 'Постов: ' : 'Posts: '}${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
    
    showChartError(chartId, error) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;
        
        const container = canvas.parentElement;
        if (container) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <p class="mt-2">${this.escapeHtml(error)}</p>
                </div>
            `;
        }
    }
    
    addPeriodListeners() {
        const buttons = document.querySelectorAll('.period-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', async () => {
                const period = btn.dataset.period;
                if (!period) return;
                
                this.currentPeriod = period;
                
                buttons.forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-secondary');
                });
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-primary');
                
                await this.loadAllStats();
            });
        });
    }
    
    async refresh() {
        await this.loadAllStats();
    }
    
    getTopPostsLimit() {
        const limitInput = document.querySelector('[name="settings[top_posts_limit]"]');
        if (limitInput && limitInput.value) {
            return parseInt(limitInput.value);
        }
        return 5;
    }
    
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showPostTooltip(event, post, type) {
        const tooltip = document.createElement('div');
        tooltip.className = 'chart-tooltip';
        tooltip.style.position = 'fixed';
        tooltip.style.left = (event.clientX + 10) + 'px';
        tooltip.style.top = (event.clientY - 30) + 'px';
        tooltip.style.zIndex = '9999';
        tooltip.innerHTML = `
            <strong>${this.escapeHtml(post.title)}</strong><br>
            ${type === 'popular' ? `👁️ ${lang === 'ru' ? 'Просмотров: ' : 'Views: '}${post.views}` : `❤️ ${lang === 'ru' ? 'Лайков: ' : 'Likes: '}${post.actual_likes || post.likes_count}`}<br>
            📅 ${new Date(post.created_at).toLocaleDateString(lang === 'ru' ? 'ru-RU' : 'en-EN')}
        `;
        document.body.appendChild(tooltip);
        
        setTimeout(() => tooltip.remove(), 3000);
    }

    exportToCSV(type = 'full') {
        const url = new URL(`${window.ADMIN_URL || '/admin'}/stats/export-csv`);
        url.searchParams.append('type', type);
        url.searchParams.append('period', this.currentPeriod);
        
        window.open(url.toString(), '_blank');
        
        this.showNotification(lang === 'ru' ? 'Экспорт CSV начался' : 'CSV export started', 'success');
    }
    
    exportToPDF() {
        const originalStyles = {};
        const charts = ['publicationsChart', 'popularPostsChart', 'likedPostsChart', 'commentsChart'];
        
        charts.forEach(chartId => {
            const canvas = document.getElementById(chartId);
            if (canvas && this.charts[chartId]) {
                originalStyles[chartId] = canvas.style.height;
                canvas.style.height = '400px';
                this.charts[chartId].resize();
            }
        });
        
        setTimeout(() => {
            window.print();
            setTimeout(() => {
                charts.forEach(chartId => {
                    const canvas = document.getElementById(chartId);
                    if (canvas && originalStyles[chartId]) {
                        canvas.style.height = originalStyles[chartId];
                        if (this.charts[chartId]) {
                            this.charts[chartId].resize();
                        }
                    }
                });
            }, 1000);
        }, 200);
        
        this.showNotification(lang === 'ru' ? 'Подготовка PDF...' : 'Preparing PDF...', 'info');
    }
    
    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 250px;
            animation: slideIn 0.3s ease;
        `;
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

}

if (typeof window.dashboardStats === 'undefined') {
    window.dashboardStats = new DashboardStats();
}
<aside class="right-side" id="ai-analytics-container">    
    <section class="content-header">
        <h1>
            AI Admissions Analytics
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>admissionsV1/admissions_report" >
                    <i class="ion ion-arrow-left-a"></i>
                    Back to Reports
                </a> 
            </small>
        </h1>     
    </section>
    <hr />
    
    <div class="content"> 
        <!-- Control Panel -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Analysis Configuration</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Academic Term</label>
                                    <select id="select-term" class="form-control" v-model="selectedTerm">
                                        <?php foreach($sy as $s): ?>
                                            <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>">
                                                <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Analysis Type</label>
                                    <select id="analysis-type" class="form-control" v-model="analysisType">
                                        <option value="comprehensive">Comprehensive Analysis</option>
                                        <option value="conversion_optimization">Conversion Optimization</option>
                                        <option value="program_analysis">Program Performance</option>
                                        <option value="temporal_analysis">Timing Analysis</option>
                                        <option value="competitive_analysis">Competitive Insights</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" v-model="startDate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" v-model="endDate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary btn-block" @click="generateAnalysis" :disabled="isLoading">
                                        <i class="fa fa-robot" v-if="!isLoading"></i>
                                        <i class="fa fa-spinner fa-spin" v-if="isLoading"></i>
                                        {{ isLoading ? 'Analyzing...' : 'Generate AI Analysis' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Dashboard -->
        <div class="row" v-if="realtimeMetrics">
            <div class="col-md-3">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Today's Applications</span>
                        <span class="info-box-number">{{ realtimeMetrics.today.registrations }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 70%"></div>
                        </div>
                        <span class="progress-description">
                            {{ realtimeMetrics.this_week.registrations }} this week
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">This Month</span>
                        <span class="info-box-number">{{ realtimeMetrics.this_month.registrations }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 85%"></div>
                        </div>
                        <span class="progress-description">
                            Monthly target progress
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-graduation-cap"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Term Total</span>
                        <span class="info-box-number">{{ realtimeMetrics.term_total.registrations }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 60%"></div>
                        </div>
                        <span class="progress-description">
                            Term target progress
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-brain"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">AI Insights</span>
                        <span class="info-box-number">{{ savedAnalyses.length }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Saved analyses
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Analysis Results -->
        <div class="row" v-if="analysisResult">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-robot"></i> AI Analysis Results
                        </h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-sm btn-default" @click="saveAnalysis">
                                <i class="fa fa-save"></i> Save Analysis
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-download"></i> Export <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" @click="exportAnalysis('pdf')">PDF Report</a></li>
                                    <li><a href="#" @click="exportAnalysis('excel')">Excel Spreadsheet</a></li>
                                    <li><a href="#" @click="exportAnalysis('csv')">CSV Data</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <!-- Executive Summary -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4><i class="fa fa-star text-yellow"></i> Executive Summary</h4>
                                <div class="alert alert-info">
                                    <div v-html="formatText(analysisResult.structured_analysis.executive_summary)"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Key Insights -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4><i class="fa fa-lightbulb text-yellow"></i> Key Insights</h4>
                                <ul class="list-group">
                                    <li class="list-group-item" v-for="insight in analysisResult.structured_analysis.key_insights">
                                        <i class="fa fa-check-circle text-green"></i> {{ insight }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h4><i class="fa fa-tasks text-blue"></i> Top Recommendations</h4>
                                <div class="recommendation-item" v-for="rec in analysisResult.recommendations.slice(0, 5)">
                                    <div class="recommendation-header">
                                        <span class="label" :class="getPriorityClass(rec.priority)">{{ rec.priority.toUpperCase() }}</span>
                                        <strong>{{ rec.title }}</strong>
                                    </div>
                                    <p class="recommendation-desc">{{ rec.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Analysis Tabs -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="nav-tabs-custom">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#detailed-analysis" data-toggle="tab">Detailed Analysis</a></li>
                                        <li><a href="#recommendations" data-toggle="tab">All Recommendations</a></li>
                                        <li><a href="#metrics" data-toggle="tab">Metrics to Track</a></li>
                                        <li><a href="#timeline" data-toggle="tab">Implementation Timeline</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="active tab-pane" id="detailed-analysis">
                                            <div v-html="formatText(analysisResult.structured_analysis.detailed_analysis)"></div>
                                        </div>
                                        <div class="tab-pane" id="recommendations">
                                            <div class="recommendation-section" v-for="priority in ['high', 'medium', 'low']">
                                                <h4 class="text-capitalize">{{ priority }} Priority Recommendations</h4>
                                                <div class="recommendation-card" v-for="rec in getRecommendationsByPriority(priority)">
                                                    <div class="card-header">
                                                        <span class="label" :class="getPriorityClass(rec.priority)">{{ rec.priority.toUpperCase() }}</span>
                                                        <strong>{{ rec.title }}</strong>
                                                    </div>
                                                    <div class="card-body">
                                                        {{ rec.description }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="metrics">
                                            <h4>Key Performance Indicators to Monitor</h4>
                                            <ul class="list-group">
                                                <li class="list-group-item" v-for="metric in analysisResult.structured_analysis.metrics_to_track">
                                                    <i class="fa fa-chart-bar text-blue"></i> {{ metric }}
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="tab-pane" id="timeline">
                                            <div v-html="formatText(analysisResult.structured_analysis.implementation_timeline)"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historical Trends -->
        <div class="row" v-if="historicalTrends">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Historical Trends</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-sm btn-default" @click="loadHistoricalTrends">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="trendsChart" width="400" height="200"></canvas>
                            </div>
                            <div class="col-md-6">
                                <h4>Trend Analysis</h4>
                                <div class="trend-item" v-for="(trend, metric) in historicalTrends.trends">
                                    <div class="trend-header">
                                        <span class="metric-name">{{ formatMetricName(metric) }}</span>
                                        <span class="trend-direction" :class="getTrendClass(trend.direction)">
                                            <i :class="getTrendIcon(trend.direction)"></i>
                                            {{ trend.direction }}
                                        </span>
                                    </div>
                                    <div class="trend-change">
                                        {{ trend.change_percentage > 0 ? '+' : '' }}{{ trend.change_percentage.toFixed(1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saved Analyses -->
        <div class="row" v-if="savedAnalyses.length > 0">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Recent AI Analyses</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Analysis Type</th>
                                        <th>Term</th>
                                        <th>Generated By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="analysis in savedAnalyses">
                                        <td>{{ formatDate(analysis.generated_at) }}</td>
                                        <td>{{ formatAnalysisType(analysis.analysis_type) }}</td>
                                        <td>{{ analysis.term_id }}</td>
                                        <td>{{ analysis.generated_by }}</td>
                                        <td>
                                            <button class="btn btn-xs btn-primary" @click="loadSavedAnalysis(analysis)">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-xs btn-default" @click="downloadAnalysis(analysis)">
                                                <i class="fa fa-download"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Display -->
        <div class="row" v-if="errorMessage">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <h4><i class="fa fa-exclamation-triangle"></i> Error</h4>
                    {{ errorMessage }}
                </div>
            </div>
        </div>
    </div>
</aside>

<style>
.recommendation-item {
    margin-bottom: 15px;
    padding: 10px;
    border-left: 4px solid #3c8dbc;
    background-color: #f9f9f9;
}

.recommendation-header {
    margin-bottom: 5px;
}

.recommendation-desc {
    margin: 0;
    color: #666;
}

.recommendation-section {
    margin-bottom: 30px;
}

.recommendation-card {
    margin-bottom: 15px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #fff;
}

.card-header {
    margin-bottom: 10px;
    font-weight: bold;
}

.card-body {
    color: #666;
}

.trend-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #eee;
    border-radius: 4px;
}

.trend-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.metric-name {
    font-weight: bold;
}

.trend-direction {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.trend-change {
    font-weight: bold;
    font-size: 16px;
}

.trend-increasing {
    background-color: #d4edda;
    color: #155724;
}

.trend-decreasing {
    background-color: #f8d7da;
    color: #721c24;
}

.trend-stable {
    background-color: #fff3cd;
    color: #856404;
}
</style>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Vue({
    el: '#ai-analytics-container',
    data: {
        base_url: '<?php echo base_url(); ?>',
        selectedTerm: '<?php echo $current_sem; ?>',
        analysisType: 'comprehensive',
        startDate: '',
        endDate: '',
        isLoading: false,
        analysisResult: null,
        historicalTrends: null,
        realtimeMetrics: null,
        savedAnalyses: [],
        errorMessage: '',
        trendsChart: null
    },

    mounted() {
        this.loadRealtimeMetrics();
        this.loadSavedAnalyses();
        this.loadHistoricalTrends();
    },

    methods: {
        generateAnalysis() {
            this.isLoading = true;
            this.errorMessage = '';
            
            const payload = {
                term: this.selectedTerm,
                analysis_type: this.analysisType,
                start_date: this.startDate,
                end_date: this.endDate
            };

            axios.post(this.base_url + 'ai_analytics/generate_analysis', payload)
                .then(response => {
                    if (response.data.success) {
                        this.analysisResult = response.data.data;
                    } else {
                        this.errorMessage = response.data.error || 'Analysis failed';
                    }
                })
                .catch(error => {
                    this.errorMessage = 'Network error: ' + error.message;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        loadRealtimeMetrics() {
            axios.post(this.base_url + 'ai_analytics/get_realtime_metrics', {
                term: this.selectedTerm
            })
            .then(response => {
                if (response.data.success) {
                    this.realtimeMetrics = response.data.data;
                }
            })
            .catch(error => {
                console.error('Failed to load realtime metrics:', error);
            });
        },

        loadHistoricalTrends() {
            axios.post(this.base_url + 'ai_analytics/get_historical_trends', {
                current_term: this.selectedTerm,
                terms_back: 6
            })
            .then(response => {
                if (response.data.success) {
                    this.historicalTrends = response.data.data;
                    this.$nextTick(() => {
                        this.renderTrendsChart();
                    });
                }
            })
            .catch(error => {
                console.error('Failed to load historical trends:', error);
            });
        },

        loadSavedAnalyses() {
            axios.get(this.base_url + 'ai_analytics/get_saved_analyses/' + this.selectedTerm)
                .then(response => {
                    if (response.data.success) {
                        this.savedAnalyses = response.data.data;
                    }
                })
                .catch(error => {
                    console.error('Failed to load saved analyses:', error);
                });
        },

        saveAnalysis() {
            if (!this.analysisResult) return;

            const payload = {
                analysis_data: this.analysisResult,
                term: this.selectedTerm,
                analysis_type: this.analysisType
            };

            axios.post(this.base_url + 'ai_analytics/save_analysis', payload)
                .then(response => {
                    if (response.data.success) {
                        alert('Analysis saved successfully!');
                        this.loadSavedAnalyses();
                    } else {
                        alert('Failed to save analysis: ' + response.data.error);
                    }
                })
                .catch(error => {
                    alert('Network error: ' + error.message);
                });
        },

        exportAnalysis(format) {
            if (!this.analysisResult) return;

            const payload = {
                analysis_data: this.analysisResult,
                format: format
            };

            axios.post(this.base_url + 'ai_analytics/export_analysis', payload)
                .then(response => {
                    if (response.data.success) {
                        window.open(response.data.download_url, '_blank');
                    } else {
                        alert('Export failed: ' + response.data.error);
                    }
                })
                .catch(error => {
                    alert('Export error: ' + error.message);
                });
        },

        formatText(text) {
            if (!text) return '';
            return text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        formatAnalysisType(type) {
            return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        formatMetricName(metric) {
            return metric.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        getPriorityClass(priority) {
            const classes = {
                'high': 'label-danger',
                'medium': 'label-warning',
                'low': 'label-info'
            };
            return classes[priority] || 'label-default';
        },

        getTrendClass(direction) {
            return 'trend-' + direction;
        },

        getTrendIcon(direction) {
            const icons = {
                'increasing': 'fa fa-arrow-up',
                'decreasing': 'fa fa-arrow-down',
                'stable': 'fa fa-minus'
            };
            return icons[direction] || 'fa fa-minus';
        },

        getRecommendationsByPriority(priority) {
            if (!this.analysisResult || !this.analysisResult.recommendations) return [];
            return this.analysisResult.recommendations.filter(rec => rec.priority === priority);
        },

        renderTrendsChart() {
            if (!this.historicalTrends || this.trendsChart) return;

            const ctx = document.getElementById('trendsChart').getContext('2d');
            const data = this.historicalTrends.historical_data;
            
            this.trendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.term_info.enumSem + ' ' + d.term_info.strYearStart),
                    datasets: [{
                        label: 'Overall Conversion Rate',
                        data: data.map(d => d.metrics.overall_conversion_rate),
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }, {
                        label: 'Payment Rate',
                        data: data.map(d => d.metrics.payment_rate),
                        borderColor: 'rgb(255, 99, 132)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        },

        loadSavedAnalysis(analysis) {
            try {
                this.analysisResult = JSON.parse(analysis.analysis_data);
            } catch (e) {
                alert('Failed to load saved analysis');
            }
        },

        downloadAnalysis(analysis) {
            // Implement download functionality
            alert('Download functionality will be implemented');
        }
    }
});
</script>

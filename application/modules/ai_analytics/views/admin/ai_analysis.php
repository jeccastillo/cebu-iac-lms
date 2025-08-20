<aside class="right-side" id="ai-analytics-container">    
    <section class="content-header">
        <h1>
            AI Admissions Analytics
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>admissionsV1/admissions_report" >
                    <i class="ion ion-arrow-left-a"></i>
                    Back to Report
                </a>
            </small>
        </h1>     
    </section>
    <hr />
    
    <div class="content" v-if="!loading"> 
        <!-- Analysis Controls -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Analysis Controls</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Analysis Type:</label>
                                <select v-model="selectedAnalysisType" class="form-control">
                                    <option value="comprehensive">Comprehensive Analysis</option>
                                    <option value="conversion_optimization">Conversion Optimization</option>
                                    <option value="program_analysis">Program Performance</option>
                                    <option value="temporal_analysis">Temporal Analysis</option>
                                    <option value="competitive_analysis">Competitive Analysis</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Term:</label>
                                <select v-model="selectedTerm" class="form-control">
                                    <option v-for="term in availableTerms" :value="term.intID">
                                        {{ term.enumSem }} {{ term.strYearStart }}-{{ term.strYearEnd }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Date Range (Optional):</label>
                                <div class="input-group">
                                    <input type="date" v-model="startDate" class="form-control" placeholder="Start Date">
                                    <span class="input-group-addon">to</span>
                                    <input type="date" v-model="endDate" class="form-control" placeholder="End Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button @click="generateAnalysis" :disabled="analyzing" class="btn btn-primary btn-block">
                                    <i class="fa fa-robot" v-if="!analyzing"></i>
                                    <i class="fa fa-spinner fa-spin" v-if="analyzing"></i>
                                    {{ analyzing ? 'Analyzing...' : 'Generate Analysis' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Dashboard -->
        <div class="row" v-if="currentAnalysis">
            <div class="col-md-3">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Applications</span>
                        <span class="info-box-number">{{ currentAnalysis.data_summary.total_applications }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-graduation-cap"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Enrolled</span>
                        <span class="info-box-number">{{ currentAnalysis.data_summary.total_enrolled }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-percent"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Conversion Rate</span>
                        <span class="info-box-number">{{ currentAnalysis.data_summary.conversion_rate.toFixed(2) }}%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Analysis Date</span>
                        <span class="info-box-number">{{ formatDate(currentAnalysis.generated_at) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Analysis Results -->
        <div class="row" v-if="currentAnalysis">
            <div class="col-md-8">
                <!-- Executive Summary -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Executive Summary</h3>
                    </div>
                    <div class="box-body">
                        <div v-html="formatText(currentAnalysis.structured_analysis.executive_summary)"></div>
                    </div>
                </div>

                <!-- Detailed Analysis -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Detailed Analysis</h3>
                    </div>
                    <div class="box-body">
                        <div v-html="formatText(currentAnalysis.structured_analysis.detailed_analysis)"></div>
                    </div>
                </div>

                <!-- Key Insights -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-eye"></i> Key Insights</h3>
                    </div>
                    <div class="box-body">
                        <ul class="list-unstyled">
                            <li v-for="insight in currentAnalysis.structured_analysis.key_insights" class="margin-bottom">
                                <i class="fa fa-check-circle text-green"></i> {{ insight }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Recommendations -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-tasks"></i> Top Recommendations</h3>
                    </div>
                    <div class="box-body">
                        <div v-for="(rec, index) in currentAnalysis.recommendations" :key="index" class="margin-bottom">
                            <div class="callout" :class="getPriorityClass(rec.priority)">
                                <h5>{{ rec.title }}</h5>
                                <p>{{ rec.description }}</p>
                                <small class="text-muted">Priority: {{ rec.priority.toUpperCase() }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-download"></i> Export Analysis</h3>
                    </div>
                    <div class="box-body">
                        <button @click="exportAnalysis('pdf')" class="btn btn-danger btn-block margin-bottom">
                            <i class="fa fa-file-pdf-o"></i> Export as PDF
                        </button>
                        <button @click="exportAnalysis('excel')" class="btn btn-success btn-block margin-bottom">
                            <i class="fa fa-file-excel-o"></i> Export as Excel
                        </button>
                        <button @click="exportAnalysis('csv')" class="btn btn-info btn-block">
                            <i class="fa fa-file-text-o"></i> Export as CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Implementation Timeline -->
        <div class="row" v-if="currentAnalysis && currentAnalysis.structured_analysis.implementation_timeline">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-clock-o"></i> Implementation Timeline</h3>
                    </div>
                    <div class="box-body">
                        <div v-html="formatText(currentAnalysis.structured_analysis.implementation_timeline)"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analysis History -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-history"></i> Analysis History</h3>
                        <div class="box-tools pull-right">
                            <button @click="loadAnalysisHistory" class="btn btn-sm btn-default">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Term</th>
                                        <th>Applications</th>
                                        <th>Conversion Rate</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="analysis in analysisHistory" :key="analysis.id">
                                        <td>{{ formatDate(analysis.generated_at) }}</td>
                                        <td>{{ analysis.analysis_type }}</td>
                                        <td>{{ analysis.term_info }}</td>
                                        <td>{{ analysis.total_applications }}</td>
                                        <td>{{ analysis.conversion_rate }}%</td>
                                        <td>
                                            <button @click="loadAnalysis(analysis.id)" class="btn btn-xs btn-primary">
                                                <i class="fa fa-eye"></i> View
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
    </div>

    <!-- Loading State -->
    <div class="content text-center" v-if="loading">
        <div class="margin-top-lg">
            <i class="fa fa-spinner fa-spin fa-3x text-muted"></i>
            <h4 class="text-muted">Loading AI Analytics...</h4>
        </div>
    </div>
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#ai-analytics-container',
    data: {
        loading: true,
        analyzing: false,
        base_url: '<?php echo base_url(); ?>',
        selectedAnalysisType: 'comprehensive',
        selectedTerm: <?php echo isset($current_sem) ? $current_sem : 'null'; ?>,
        startDate: '',
        endDate: '',
        availableTerms: <?php echo isset($sy) ? json_encode($sy) : '[]'; ?>,
        currentAnalysis: null,
        analysisHistory: []
    },

    mounted() {
        this.loading = false;
        this.loadAnalysisHistory();
        
        // Set default term if available
        if (this.availableTerms.length > 0 && !this.selectedTerm) {
            this.selectedTerm = this.availableTerms[0].intID;
        }
    },

    methods: {
        generateAnalysis() {
            if (!this.selectedTerm) {
                alert('Please select a term');
                return;
            }

            this.analyzing = true;
            
            const payload = {
                analysis_type: this.selectedAnalysisType,
                term: this.selectedTerm,
                start_date: this.startDate || null,
                end_date: this.endDate || null
            };

            axios.post(this.base_url + 'ai_analytics/generate_analysis', payload)
                .then(response => {
                    if (response.data.success) {
                        this.currentAnalysis = response.data.data;
                        this.loadAnalysisHistory(); // Refresh history
                    } else {
                        alert('Analysis failed: ' + (response.data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Analysis error:', error);
                    alert('Analysis failed. Please check the console for details.');
                })
                .finally(() => {
                    this.analyzing = false;
                });
        },

        loadAnalysisHistory() {
            axios.get(this.base_url + 'ai_analytics/get_saved_analyses')
                .then(response => {
                    if (response.data.success) {
                        this.analysisHistory = response.data.data || [];
                    }
                })
                .catch(error => {
                    console.error('Failed to load analysis history:', error);
                });
        },

        loadAnalysis(analysisId) {
            // For now, just show a message since we need to implement this endpoint
            alert('Analysis loading feature will be implemented in the next update');
        },

        exportAnalysis(format) {
            if (!this.currentAnalysis) {
                alert('No analysis to export');
                return;
            }

            const url = this.base_url + 'ai_analytics/export_analysis/' + this.currentAnalysis.id + '/' + format;
            window.open(url, '_blank');
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        },

        formatText(text) {
            if (!text) return '';
            
            // Convert markdown-style formatting to HTML
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>')
                .replace(/^- (.*)/gm, '<li>$1</li>')
                .replace(/^## (.*)/gm, '<h4>$1</h4>')
                .replace(/^# (.*)/gm, '<h3>$1</h3>');
        },

        getPriorityClass(priority) {
            switch (priority) {
                case 'high': return 'callout-danger';
                case 'medium': return 'callout-warning';
                case 'low': return 'callout-info';
                default: return 'callout-default';
            }
        }
    }
});
</script>

<style>
.margin-top-lg {
    margin-top: 50px;
}

.callout {
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #eee;
    border-left-width: 5px;
    border-radius: 3px;
}

.callout-danger {
    border-left-color: #d73925;
    background-color: #fcf2f2;
}

.callout-warning {
    border-left-color: #f39c12;
    background-color: #fefefe;
}

.callout-info {
    border-left-color: #3c8dbc;
    background-color: #f4f8fa;
}

.callout-default {
    border-left-color: #777;
    background-color: #f9f9f9;
}

.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-top-left-radius: 2px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 2px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
}

.info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 13px;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.bg-aqua { background-color: #00c0ef !important; }
.bg-green { background-color: #00a65a !important; }
.bg-yellow { background-color: #f39c12 !important; }
.bg-red { background-color: #dd4b39 !important; }
</style>

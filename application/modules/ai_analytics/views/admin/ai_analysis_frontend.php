<aside class="right-side" id="ai-analytics-container">    
    <section class="content-header">
        <h1>
            AI Admissions Analytics - Frontend API Version
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
        <!-- System Status -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">System Status</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>API URL:</strong> {{ apiUrl }}
                            </div>
                            <div class="col-md-3">
                                <strong>Current Term:</strong> {{ selectedTerm }}
                            </div>
                            <div class="col-md-3">
                                <strong>Campus:</strong> {{ campus }}
                            </div>
                            <div class="col-md-3">
                                <strong>Token:</strong> {{ token ? 'Available' : 'Not Available' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Testing -->
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Step 1: Test Admissions API</h3>
                    </div>
                    <div class="box-body">
                        <p>First, let's fetch data from the admissions API:</p>
                        <button @click="fetchAdmissionsData" :disabled="fetchingData" class="btn btn-primary">
                            <i class="fa fa-download" v-if="!fetchingData"></i>
                            <i class="fa fa-spinner fa-spin" v-if="fetchingData"></i>
                            {{ fetchingData ? 'Fetching...' : 'Fetch Admissions Data' }}
                        </button>
                        
                        <div v-if="admissionsApiData" class="margin-top">
                            <h5>API Data Retrieved:</h5>
                            <div class="well well-sm">
                                <strong>Paid:</strong> {{ admissionsApiData.paid }}, 
                                <strong>Unpaid:</strong> {{ admissionsApiData.unpaid }}, 
                                <strong>Enrolled:</strong> {{ admissionsApiData.enrolled }}
                                <br>
                                <small>Total fields: {{ Object.keys(admissionsApiData).length }}</small>
                            </div>
                        </div>
                        
                        <div v-if="apiError" class="alert alert-danger margin-top">
                            <strong>API Error:</strong> {{ apiError }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Step 2: Generate AI Analysis</h3>
                    </div>
                    <div class="box-body">
                        <p>Now let's send the data to our AI analysis backend:</p>
                        
                        <div class="form-group">
                            <label>Analysis Type:</label>
                            <select v-model="analysisType" class="form-control">
                                <option value="comprehensive">Comprehensive Analysis</option>
                                <option value="conversion_optimization">Conversion Optimization</option>
                                <option value="program_analysis">Program Performance</option>
                                <option value="temporal_analysis">Temporal Analysis</option>
                                <option value="competitive_analysis">Competitive Analysis</option>
                            </select>
                        </div>
                        
                        <button @click="generateAnalysis" :disabled="!admissionsApiData || analyzing" class="btn btn-success">
                            <i class="fa fa-robot" v-if="!analyzing"></i>
                            <i class="fa fa-spinner fa-spin" v-if="analyzing"></i>
                            {{ analyzing ? 'Analyzing...' : 'Generate AI Analysis' }}
                        </button>
                        
                        <div v-if="!admissionsApiData" class="text-muted margin-top">
                            <small>Please fetch admissions data first</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analysis Results -->
        <div class="row" v-if="analysisResult">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">AI Analysis Results</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h5>Data Summary</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Term:</strong> {{ analysisResult.data_summary.term }}</li>
                                    <li><strong>Applications:</strong> {{ analysisResult.data_summary.total_applications }}</li>
                                    <li><strong>Enrolled:</strong> {{ analysisResult.data_summary.total_enrolled }}</li>
                                    <li><strong>Conversion Rate:</strong> {{ analysisResult.data_summary.conversion_rate.toFixed(2) }}%</li>
                                </ul>
                            </div>
                            <div class="col-md-8">
                                <h5>Executive Summary</h5>
                                <div class="well">
                                    <div v-html="formatText(analysisResult.structured_analysis.executive_summary)"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" v-if="analysisResult.recommendations && analysisResult.recommendations.length > 0">
                            <div class="col-md-12">
                                <h5>Top Recommendations</h5>
                                <div class="row">
                                    <div v-for="(rec, index) in analysisResult.recommendations.slice(0, 3)" :key="index" class="col-md-4">
                                        <div class="callout" :class="getPriorityClass(rec.priority)">
                                            <h6>{{ rec.title }}</h6>
                                            <small>Priority: {{ rec.priority.toUpperCase() }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Display -->
        <div class="row" v-if="analysisError">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <h4>Analysis Error</h4>
                    <p>{{ analysisError }}</p>
                </div>
            </div>
        </div>

        <!-- Debug Information -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Debug Information</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <h5>Raw API Data:</h5>
                        <pre>{{ JSON.stringify(admissionsApiData, null, 2) }}</pre>
                        
                        <h5>Raw Analysis Result:</h5>
                        <pre>{{ JSON.stringify(analysisResult, null, 2) }}</pre>
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
        loading: false,
        fetchingData: false,
        analyzing: false,
        baseUrl: '<?php echo base_url(); ?>',
        apiUrl: '<?php echo $this->config->item('api_url') ?: base_url() . 'api/'; ?>',
        selectedTerm: <?php echo isset($current_sem) ? $current_sem : 'null'; ?>,
        campus: '<?php echo isset($campus) ? $campus : '1'; ?>',
        token: '<?php echo isset($_SESSION['token']) ? $_SESSION['token'] : ''; ?>',
        analysisType: 'comprehensive',
        admissionsApiData: null,
        analysisResult: null,
        apiError: null,
        analysisError: null
    },

    mounted() {
        console.log('AI Analytics Frontend Version - Loaded');
        console.log('API URL:', this.apiUrl);
        console.log('Current Term:', this.selectedTerm);
        console.log('Campus:', this.campus);
    },

    methods: {
        fetchAdmissionsData() {
            this.fetchingData = true;
            this.apiError = null;
            this.admissionsApiData = null;
            
            // Build the API URL like in admissions_report.php
            const queryParams = new URLSearchParams({
                current_sem: this.selectedTerm,
                campus: this.campus
            });
            
            const apiEndpoint = this.apiUrl + 'admissions/applications/adstats?' + queryParams.toString();
            
            console.log('Fetching from:', apiEndpoint);
            
            // Set up headers
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            
            if (this.token) {
                headers['Authorization'] = 'Bearer ' + this.token;
            }
            
            axios.get(apiEndpoint, { headers })
                .then(response => {
                    console.log('API Response:', response.data);
                    this.admissionsApiData = response.data;
                })
                .catch(error => {
                    console.error('API Error:', error);
                    this.apiError = error.response ? 
                        `HTTP ${error.response.status}: ${error.response.statusText}` : 
                        error.message;
                })
                .finally(() => {
                    this.fetchingData = false;
                });
        },

        generateAnalysis() {
            if (!this.admissionsApiData) {
                alert('Please fetch admissions data first');
                return;
            }

            this.analyzing = true;
            this.analysisError = null;
            this.analysisResult = null;
            
            const payload = {
                analysis_type: this.analysisType,
                term: this.selectedTerm,
                api_data: this.admissionsApiData // Send the fetched API data
            };
            
            console.log('Sending to backend:', payload);
            
            axios.post(this.baseUrl + 'ai_analytics/generate_analysis', payload)
                .then(response => {
                    console.log('Analysis Response:', response.data);
                    if (response.data.success) {
                        this.analysisResult = response.data.data;
                    } else {
                        this.analysisError = response.data.error || 'Unknown error occurred';
                    }
                })
                .catch(error => {
                    console.error('Analysis Error:', error);
                    this.analysisError = error.response ? 
                        `HTTP ${error.response.status}: ${error.response.data.error || error.response.statusText}` : 
                        error.message;
                })
                .finally(() => {
                    this.analyzing = false;
                });
        },

        formatText(text) {
            if (!text) return '';
            
            // Convert markdown-style formatting to HTML
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>')
                .replace(/^- (.*)/gm, '<li>$1</li>')
                .replace(/^## (.*)/gm, '<h5>$1</h5>')
                .replace(/^# (.*)/gm, '<h4>$1</h4>');
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
.margin-top { margin-top: 15px; }
.margin-top-lg { margin-top: 50px; }

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

pre {
    max-height: 300px;
    overflow-y: auto;
}
</style>

<template>
    <div class="jobChecker">
        <div v-if="status == null">
            <span>Waiting job {{jobId}} {{statusMessage}}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="48px" height="48px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#3bb21f" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
                    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
                </circle>
            </svg>
        </div>
        <div :class="getAlertClass()" v-if="status != null">Job status: {{statusMessage}}</div>

    </div>
</template>

<script>
    export default {
        name: "jobChecker",
        props: ["jobId", "csrf"],
        data() {
            return {
                "status": null,
                "statusMessage" : "",
            }
        },
        methods: {
            setStatus(status, message) {
                this.statusMessage = message;
                this.status = status;
                this.$emit("complete", {status, message});
            },
            getAlertClass() {
                return "alert alert-" + (this.status == false ? 'danger' : 'success');
            },
            checkJobStatus() {
                console.log("checking status of " + this.jobId);
                const _this = this;
                const options = {
                    method: 'post',
                    url: '/catalogue/checkJob',
                    data: {id: this.jobId },
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    }
                };
                axios(options)
                    .then(function (e) {
                        console.log('successfull response', e);
                        if (!e.data.status) {
                            _this.setStatus(false, e.data.errors[0]);
                            return;
                        }

                        const jobStatus = e.data.data.jobStatus;
                        if (jobStatus == "pending") {
                            window.setTimeout(_this.checkJobStatus, 3000);
                            _this.statusMessage = "Added products: " + e.data.data.productsCount;
                            return;
                        }

                        const status = jobStatus == "success" ? true : false;
                        const message = status ? "File successfully parsed" : e.data.data.message;
                        _this.setStatus(status, message);

                    })
                    .catch(function (e) {
                        console.log("error response", e);
                        _this.setStatus(false, e);
                    });
            }
        },
        mounted() {
            console.log("Mounted");
            this.checkJobStatus();
        },
    }
</script>

<style scoped>
    svg {
        display: inline-block;
    }
</style>
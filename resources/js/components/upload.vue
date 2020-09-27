<template>
    <div>
        <h1>Upload catalogue file</h1>
        <div :class="getAlertClass()" v-if="status != null">{{statusMessage}}</div>
        <job-checker v-on:complete="onJobComplete($event)" v-if="jobId" :jobId="jobId" :csrf="csrf"></job-checker>
        <input :disabled="blocked" v-on:change="handleFileUpload($event)" name='xml' ref="file" type="file"/>
        <button :disabled="blocked" v-on:click="uploadCatalogue()">Upload</button>
    </div>
</template>

<script>
    import JobChecker from "./jobChecker";

    export default {
        name: "upload",
        components: {JobChecker},
        data: function() {
            return {
                csrf: window.CSRF_TOKEN,
                status: null,
                statusMessage: null,
                file: '',
                jobId: null,
                blocked: false,
            }
        },
        methods: {
            onJobComplete(e) {
                this.blocked = false;
                this.$refs.file.value = "";
                this.file = null;
            },
            uploadCatalogue() {
                console.log("Upload", this);
                let formData = new FormData();
                formData.append('xml', this.file);
                // console.log(formData);
                const _this = this;
                _this.blocked = true;
                const options = {
                    method: 'post',
                    url: '/catalogue/upload',
                    data: formData,
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': this.csrf,
                    }
                };
                axios(options)
                    .then(function (e) {
                        console.log('successfull response',e);
                        if (!e.data.status) {
                            _this.statusMessage = e.data.errors[0];
                            _this.status = false;
                            _this.blocked = false;
                            return;
                        }
                        _this.statusMessage = "File has been successfuly uploaded";
                        _this.status = true;
                        _this.jobId = e.data.data.jobId;

                    })
                    .catch(function (e) {
                        console.log("error response",e);
                        _this.statusMessage = e;
                        _this.status = false;
                        _this.blocked = false;
                    });
            },

            handleFileUpload(e) {
                this.file = this.$refs.file.files[0];
                this.status = null;
                this.jobId = null;
            },

            getAlertClass() {
                return "alert alert-" + (this.status == false ? 'danger' : 'success');
            }
        }
    }
</script>

<style scoped>

</style>
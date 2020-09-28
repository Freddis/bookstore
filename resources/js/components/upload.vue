<template>
    <div class="page-upload">
        <h1>Upload catalogue file</h1>
        <div :class="getAlertClass()" v-if="status != null">{{statusMessage}}</div>
        <job-checker v-on:complete="onJobComplete($event)" v-if="jobId" :jobId="jobId" :csrf="csrf"></job-checker>
        <input :disabled="blocked" v-on:change="handleFileUpload($event)" name='xml' ref="file" type="file"/>
        <button class="btn btn-primary" :disabled="blocked" v-on:click="uploadCatalogue()">Upload</button>
        <span v-if="blocked">{{percentage}}%</span>
    </div>
</template>

<script>
    import JobChecker from "./jobChecker";

    export default {
        name: "upload",
        components: {JobChecker},
        data: function () {
            return {
                csrf: window.CSRF_TOKEN,
                status: null,
                statusMessage: null,
                file: '',
                jobId: null,
                blocked: false,
                percentage: 0
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
                this.upload(this.file, "/catalogue/upload", this.csrf, function (e) {
                    console.log('successfull response', e);
                    if (!e.data.status) {
                        _this.statusMessage = e.data.errors[0];
                        _this.status = false;
                        _this.blocked = false;
                        return;
                    }
                    _this.statusMessage = "File has been successfuly uploaded";
                    _this.status = true;
                    _this.jobId = e.data.data.jobId;

                }, function (e) {
                    console.log("error response", e);
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
            },

            upload(file, url, csrf, success, failure) {
                const _self = this;
                _self.percentage = 0;
                // take the file from the input
                const reader = new FileReader();
                reader.readAsBinaryString(file); // alternatively you can use readAsDataURL
                reader.onloadend = function (evt) {
                    // create XHR instance
                    const xhr = new XMLHttpRequest();

                    // send the file through POST
                    xhr.open("POST", url, true);
                    xhr.setRequestHeader("X-CSRF-TOKEN", csrf);

                    // make sure we have the sendAsBinary method on all browsers
                    XMLHttpRequest.prototype.mySendAsBinary = function (text) {
                        var data = new ArrayBuffer(text.length);
                        var ui8a = new Uint8Array(data, 0);
                        for (var i = 0; i < text.length; i++) ui8a[i] = (text.charCodeAt(i) & 0xff);

                        if (typeof window.Blob == "function") {
                            var blob = new Blob([data]);
                        } else {
                            var bb = new (window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder)();
                            bb.append(data);
                            var blob = bb.getBlob();
                        }

                        this.send(blob);
                    };

                    // let's track upload progress
                    var eventSource = xhr.upload || xhr;
                    eventSource.addEventListener("progress", function (e) {
                        // get percentage of how much of the current file has been sent
                        var position = e.position || e.loaded;
                        var total = e.totalSize || e.total;
                        var percentage = Math.round((position / total) * 100);
                        // here you should write your own code how you wish to proces this
                        if(percentage - _self.percentage > 5 || percentage == 100) {
                            console.log("percentage", _self, percentage);
                            _self.percentage = percentage;
                        }
                    });

                    // state change observer - we need to know when and if the file was successfully uploaded
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4) {
                            if (xhr.status == 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    console.log(response);
                                    success({data: response});
                                } catch (e) {
                                    failure(e.message);
                                }
                            } else {
                                failure(xhr.statusText);
                            }
                        }
                    };

                    // start sending
                    xhr.mySendAsBinary(evt.target.result);
                };
            }
        }
    }
</script>
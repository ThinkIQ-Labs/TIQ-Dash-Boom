<script type="text/x-template" id="title-component-template">
    <div style="height:100%; position:relative;">
        
        <div v-if="showConfigCog" style="position:absolute;top:0;right:3px;cursor:pointer;z-index:99;">
            <button type="button" class="btn btn-sm btn-link"  data-bs-toggle="modal" :data-bs-target="`#modal_${id}`">
                <i class="fa-solid fa-cog" :style="{color: configComplete ? 'green' : 'red'}"></i>
            </button>
        </div>

        <div class="ms-2 display-6">{{choosenTitle}}</div>


        <!-- Modal -->
        <div class="modal" :id="`modal_${id}`" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Title Configuration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <label class="input-group-text" for="titleSelector">Title</label>
                            <input type="text" class="form-control" v-model="choosenTitle" placeholder="" aria-label="" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dismis + Close</button>
                        <button type="button" class="btn btn-primary" @click="SaveChoices">Save + Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<script>
    var WinDoc = window.document;

    var titleComponent = {
        template: '#title-component-template',
        props: ['id', 'config', 'showConfigCog'],
        data() {
            return { 

                configComplete: false,
                choosenTitle: ''
            }
        },
        mounted: async function(){
            
            this.choosenTitle = this.config.title;

            this.configComplete = this.CheckConfigIsComplete();

            const myModal = WinDoc.getElementById(`modal_${this.id}`)
            myModal.addEventListener('shown.bs.modal', () => {
                this.$emit('config-state-changed', {
                    id: this.id,
                    isConfig: true
                });
            })
            myModal.addEventListener('hidden.bs.modal', () => {
                this.$emit('config-state-changed', {
                    id: this.id,
                    isConfig: false
                });
            })
        },
        computed: {
            
        },
        methods: {

            CheckConfigIsComplete: function(){
                if(this.choosenTitle == '') return false;

                return true;
            },
            HideModal: function() {
                $(`#modal_${this.id}`).modal('hide');
            },
            SaveChoices: function(){
                this.$emit('on-save', {
                    id: this.id,
                    config: {
                        title: this.choosenTitle,
                    }
                });
                this.HideModal();
                this.configComplete = this.CheckConfigIsComplete();
            },
        }
    }
</script>

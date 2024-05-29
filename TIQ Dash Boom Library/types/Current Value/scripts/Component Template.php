<script type="text/x-template" id="live-value-component-template">
    <div style="height:100%; position:relative;">
        
        <div v-if="showConfigCog" style="position:absolute;top:0;right:3px;cursor:pointer;z-index:99;">
            <button type="button" class="btn btn-sm btn-link"  data-bs-toggle="modal" :data-bs-target="`#modal_${id}`">
                <i class="fa-solid fa-cog" :style="{color: configComplete ? 'green' : 'red'}"></i>
            </button>
        </div>

        <div v-if="currentVST!=null">
            Value: {{currentVST.v}}<br />
            TS: {{currentVST.t}}<br />
            Status: {{currentVST.s}}<br />
        </div>

        <!-- Modal -->
        <div class="modal" :id="`modal_${id}`" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Attribute Id Configuration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <label class="input-group-text" for="attrIdSelector">Attribute Id</label>
                            <input type="text" class="form-control" v-model="choosenAttrId" placeholder="" aria-label="" aria-describedby="basic-addon1">
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

    var liveValueComponent = {
        template: '#live-value-component-template',
        props: ['id', 'config', 'subscriptions', 'showConfigCog'],
        data() {
            return { 
                configComplete: false,
                choosenAttrId: '',
                currentVST: null
            }
        },
        watch: {
            subscriptions: function(newValue, oldValue){
                // console.log('subscriptions change:', newValue, oldValue);
                this.currentVST = newValue.currentVST;
            }
        },
        mounted: async function(){
            
            this.choosenAttrId = this.config.attrId;

            this.configComplete = this.CheckConfigIsComplete();

        },
        methods: {
            CheckConfigIsComplete: function(){

                if(this.choosenAttrId == '') return false;

                this.$emit('update-subscriptions', {
                    [this.id]:{
                        attrIds: [this.choosenAttrId],
                        subscriptionProps: ['currentVST']
                    }
                });

                return true;
            },
            HideModal: function() {
                $(`#modal_${this.id}`).modal('hide');
            },
            SaveChoices: function(){
                this.$emit('on-save', {
                    id: this.id,
                    config: {
                        attrId: this.choosenAttrId,
                    }
                });
                this.HideModal();
                this.configComplete = this.CheckConfigIsComplete();
            },
        }
    }
</script>
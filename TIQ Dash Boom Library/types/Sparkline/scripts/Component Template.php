<script type="text/x-template" id="spark-line-component-template">
    <div style="height:100%; position:relative;">
        
        <div v-if="showConfigCog" style="position:absolute;top:0;right:3px;cursor:pointer;z-index:99;">
            <button type="button" class="btn btn-sm btn-link"  data-bs-toggle="modal" :data-bs-target="`#modal_${id}`">
                <i class="fa-solid fa-cog" :style="{color: configComplete ? 'green' : 'red'}"></i>
            </button>
        </div>

        <div class="display-6" style="font-size=6rem;">{{choosenSparkTitle}}</div>
        <sparkline-chart
            :id='choosenAttrId'
            :duration='60 * 60 * 1'
            :offset='0'
            :live-mode='true'
            :refresh-interval='10'
            	
            :show-x-axis='true'
            :show-y-axis='true'
            :show-border='false'
            :show-tooltip='true'
            :round-y-axis-to-significant-digit='1'
            :min-range='0'
            :max-range='5'
            :x-ticks='10'
            :y-ticks='5'
            height='100'

            :margin='{
                "top": 10,
                "bottom": 30,
                "left": 30,
                "right": 10
            }'
            
        ></sparkline-chart>

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
                            <label class="input-group-text" for="sparkTitleSelector">SparkLine Title</label>
                            <input type="text" class="form-control" v-model="choosenSparkTitle" placeholder="" aria-label="" aria-describedby="basic-addon1">
                        </div>
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

    var sparkLineComponent = {
        template: '#spark-line-component-template',
        props: ['id', 'config', 'showConfigCog'],
        data() {
            return { 
                configComplete: false,
                choosenAttrId: '',
                choosenSparkTitle: '',
            }
        },
        mounted: async function(){
            
            this.choosenAttrId = this.config.attrId;
            this.choosenSparkTitle = this.config.sparkTitle;

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
        methods: {
            CheckConfigIsComplete: function(){
                if(this.choosenAttrId == '') return false;
                if(this.choosenSparkTitle == '') return false;

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
                        sparkTitle: this.choosenSparkTitle
                    }
                });
                this.HideModal();
                this.configComplete = this.CheckConfigIsComplete();
            },
        }
    }
</script>
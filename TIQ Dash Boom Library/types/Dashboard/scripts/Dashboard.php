<?php
    use Joomla\CMS\HTML\HTMLHelper;

    $primary_domain = 'https://' . $_SERVER['HTTP_HOST'];

    HTMLHelper::_('script', "$primary_domain/media/com_thinkiq/js/dist/tiq.core.js", array('version' => 'auto', 'relative' => false, 'detectDebug' => false));
    // HTMLHelper::_('script', "$primary_domain/media/com_thinkiq/js/dist/tiq.tiqGraphQL.js", array('version' => 'auto', 'relative' => false, 'detectDebug' => false));
    HTMLHelper::_('script', "$primary_domain/media/com_thinkiq/js/dist/tiq.components.js", array('version' => 'auto', 'relative' => false, 'detectDebug' => false));
    // HTMLHelper::_('script', "$primary_domain/media/com_thinkiq/js/dist/tiq.charts.js", array('version' => 'auto', 'relative' => false, 'detectDebug' => false));

    require_once 'thinkiq_context.php';
    $context = new Context();

    TiqUtilities\Model\Script::includeScript('tiq_dash_boom_library.dashboard_js_sdk');

    use TiqUtilities\Model\Script;
    use TiqUtilities\Model\Node;
    use TiqUtilities\Model\Type;
    use TiqUtilities\Model\EnumerationType;

    // load tab title
    $aHostId = $context->std_inputs->node_id;
    if($aHostId==0){
        $tabTitle = "Tab Title";
        $pageTitle = "Page Title";
    } else {
        $aHost = new Node($aHostId);
        $aHost->getAttributes();
        $tabTitle = $aHost->attributes['tab_title']->string_value;
        $pageTitle = $aHost->attributes['page_title']->string_value;
    }

    function GetSubtypes($aType){
        $aResponse = Node::GetDb()->run("select * from model.types where sub_type_of_id=$aType->id")->fetchAll();
        $typeList = [];
        foreach($aResponse as $aRecord){
            $aType = new Type($aRecord['id']);
            $typeList[] = $aType;
        }
        return $typeList;
    }

    function PostgresArrayToPhpArray($postgresArray){
        return (fn () => $this->convertPostgresTypeArray($postgresArray))->call(new Node()); 
    }


    // load widgets
    $aWidgetBaseObject = new Type('tiq_dash_boom_library.widget');
    $widgets = GetSubtypes($aWidgetBaseObject);
    $widgetsLight = [];
    foreach($widgets as $aWidget){
        $aWidget->getScripts();
        // Script::includeScript("tiq_dash_boom_library.$aWidget->relative_name.componet_template");
        Script::includeScript($aWidget->scripts['component_template']->id);
        $aWidget->getAttributes();
        $widgetsLight[] = Array(
            'componentName' => $aWidget->attributes['component_name']->default_string_value, 
            'label' => $aWidget->attributes['label']->default_string_value, 
            'icon' => $aWidget->attributes['label']->icon,
            'fqn' => PostgresArrayToPhpArray($aWidget->fqn),
            'category' => $aWidget->attributes['category']->default_enumeration_value,
        );
    }

    // load categories
    $widgetCategories = new EnumerationType('tiq_dash_boom_library.widget_categories');
?>

<div id="app">
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel" style="width:300px;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">TIQ Dash Boom</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <h5 class="offcanvas-title mt-4 mb-2" >Dashboard</h5>

            <label>Title:</label><br/>
            <input v-model="pageTitle" class="w-100"/>

            <label>Browser Tab Title:</label><br/>
            <input v-model="tabTitle" class="w-100"/>

            <h5 class="offcanvas-title my-4" >Widgets</h5>

            <div v-for="aWidgetCategory in widgetCategories">
                <div v-if="widgets.filter(x=>x.category==aWidgetCategory).length>0">
                    <h6>{{aWidgetCategory}}</h6>
                    <div v-for="aWidget in widgets.filter(x=>x.category==aWidgetCategory)" class="my-2">
                        <button class="btn btn-primary w-100" @click="AddWidget(aWidget)"><i class="me-2" :class="aWidget.icon" > </i>{{aWidget.label}}</button>
                    </div>    
                </div>
            </div>
            <div v-if="widgets.filter(x=>x.category==null).length>0">
                <h6>Mavericks</h6>
                <div v-for="aWidget in widgets.filter(x=>x.category==null)" class="my-2">
                    <button class="btn btn-primary w-100" @click="AddWidget(aWidget)"><i class="me-2" :class="aWidget.icon" > </i>{{aWidget.label}}</button>
                </div>    
            </div>
        </div>
        <div class="offcanvas-footer">
            <hr />
            <div class="row">
                <div class="col">
                    <button class="btn btn-light w-100" @click="CopyLayoutToClipboard">Copy Layout to Clipboard</button>
                </div>
                <div class="col">
                    <button class="btn btn-light w-100" @click="PasteLayoutFromClipboard">Paste Layout from Clipboard</button>
                </div>
            </div>
           <hr />
            <button class="btn btn-light w-100" @click="SaveDashboardAsync"><i class="me-2 fa-light fa-save" > </i>Save Dashboard</buttong>
        </div>
    </div>

    <div class="row">            
        <div class="col-12">
            <h1 class="pb-2 pt-2" style="font-size:2.5rem; color:#126181;">
                {{pageTitle}}

                <span v-if="dashboardIsEditMode" class="float-end ms-1  mb-1" >
                    <button class="btn btn-link mb-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" >
                        <i class="fa fa-cog" ></i>
                    </button>
                </span>

                <span class="float-end ms-1  mb-1" >
                    <button class="btn btn-link mb-1" @click="dashboardIsEditMode = !dashboardIsEditMode">
                        <i v-bind:class="dashboardIsEditMode ? 'fa fa-unlock' : 'fa fa-lock'" data-toggle="tooltip" title="Toggle Edit Mode."></i>
                    </button>
                </span>

                <a v-if="false" class="float-end btn btn-sm btn-link mt-2" style="font-size:1rem; color:#126181;" v-bind:href="`/index.php?option=com_modeleditor&view=script&id=${context.std_inputs.script_id}`" target="_blank">source</a>
            </h1>
            <hr style="border-color:#126181; border-width:medium;" />
        </div>   
    </div>

    <div id="content">
        <grid-layout style="border:0px solid black;"
                        ref="grid_layout"
                        :layout.sync="layout"
                        :col-num="50"
                        :row-height="20"
                        :auto-size="true"
                        :is-draggable="dashboardIsEditMode"
                        :is-resizable="dashboardIsEditMode"
                        :use-css-transforms="false"
                        :vertical-compact="false"
                        :prevent-collision="true"
                        @layout-created="layoutCreatedEvent"
                        @layout-before-mount="layoutBeforeMountEvent"
                        @layout-mounted="layoutMountedEvent"
                        @layout-ready="layoutReadyEvent"
                        @layout-updated="layoutUpdatedEvent"
            >
                <grid-item v-for="item in layout"
                            :key="item.i"
                            :componentName="item.componentName"
                            :typeFqn="item.typeFqn"
                            :config="item.config"
                            :i="item.i"
                            :x="item.x"
                            :y="item.y"
                            :w="item.w"
                            :wPx="item.wPx"
                            :h="item.h"
                            :hPx="item.hPx"
                            :moved="item.moved"
                            @resize="resizeEvent"
                            @move="moveEvent"
                            @resized="resizedEvent"
                            @container-resized="containerResizedEvent"
                            @moved="movedEvent"
                            :style="dashboardIsEditMode ? 'border: 1px solid black;': 'border: 0px solid black;'"
                >
                    <component :is="item.componentName" 
                        :id="item.i"  
                        :config="item.config"
                        :subscriptions="item.subscriptions"
                        :h-px="item.hPx" :w-px="item.wPx" 
                        :show-config-cog="dashboardIsEditMode"
                        @update-subscriptions="UpdateSubscriptions"
                        @on-save="SaveConfig">
                    >
                    </component>

                    <i v-if="dashboardIsEditMode" class="fa-light fa-trash-xmark" style="cursor: pointer; transform: translate(3px,-21px);" @click="removeStepFromGrid(item.i)"></i>
            </GridItem>
        </GridLayout>
    </div>
</div>

<script>
    var WinDoc = window.document;
    WinDoc.body.style.overflowX = "hidden";

    // this is the coolest delay function ever
    // use like this to wait 100ms: await delay(100);
    // https://levelup.gitconnected.com/how-to-turn-settimeout-and-setinterval-into-promises-6a4977f0ace3
    function delay(time) {
        return new Promise(resolve => setTimeout(resolve, time));
    }

    // we need a clipboard so we can copy / paste
    var clipboard = navigator.clipboard;

    function Panel(aComponentName, aTypeFqn, aLabel, aConfig, aSubscriptions, aW, aH){
        this.componentName = aComponentName;
        this.typeFqn = aTypeFqn;
        this.config = aConfig;
        this.subscriptions = aSubscriptions;
        this.i= aLabel.replaceAll(' ', '') + '_' + crypto.randomUUID().split('-')[0];
        this.x= 30;
        this. y= 0;
        this.w= aW;
        this.wPx= 0;
        this.h= aH;
        this.hPx= 0;
        this.moved= false;
    }

    var appJson = {
        components: {
           GridLayout: VueGridLayout.GridLayout,
           GridItem: VueGridLayout.GridItem
        },
        data() {
            return {
                context:<?php echo json_encode($context)?>,
                tabTitle: <?php echo json_encode($tabTitle)?>,
                pageTitle: <?php echo json_encode($pageTitle)?>,
                layout: [],
                dashboardIsEditMode: false,
                widgets: <?php echo json_encode($widgetsLight)?>,
                widgetCategories: <?php echo json_encode($widgetCategories->enumeration_names)?>,
                subscriptions:{}
            }
        },
        mounted: async function(){
            WinDoc.title = this.tabTitle;

            let aConfig = await DashboardSdk.LoadDashboardAsync(this.context.std_inputs.node_id);

            this.pageTitle = aConfig.pageTitle;
            this.tabTitle = aConfig.tabTitle;
            this.layout = aConfig.layout;
            this.FillSubscriptionsRunner();
        },
        watch: {
            tabTitle: function(a,b){
                WinDoc.title = this.tabTitle;
            }
        },
        methods: {
            CopyLayoutToClipboard: function(){
                clipboard.writeText(JSON.stringify(this.layout, null, 2));
            },
            PasteLayoutFromClipboard: async function(){
                this.layout = JSON.parse(await clipboard.readText());
            },
            FillSubscriptionsRunner: async function(){
                // this is a routine that calls itself every 10sec

                let attrIds = Object.values(this.subscriptions).map(x=>x.attrIds).flat().filter(x=>x).filter(x=>Number.isInteger(parseInt(x)));
                if(attrIds.length>0){
                    await this.FillSubscriptions();
                }
                await delay(10000);
                this.FillSubscriptionsRunner();
            },

            FillSubscriptions: async function(aSingleSubscription=null){
                // one tag: /index.php?option=com_thinkiq&task=node.show&node_id=11342607
                // multiple tags: /index.php?option=com_thinkiq&task=node.getLiveData&ids=11342603,11342607&getTimeSeriesData=0
                let ids = [];
                let aSingleSubscriptionKey = aSingleSubscription==null ? '' : Object.keys(aSingleSubscription)[0];
                if(aSingleSubscription==null){
                    // use filter(x=>x) to filter out 'undefined'
                    // use filter(x=>Number.isInteger(parseInt(x))) to make sure the attrIds are numberic
                    ids = Object.values(this.subscriptions).map(x=>x.attrIds).flat().filter(x=>x).filter(x=>Number.isInteger(parseInt(x)));
                } else {
                    ids = this.subscriptions[aSingleSubscriptionKey].attrIds.filter(x=>x).filter(x=>Number.isInteger(parseInt(x)));
                }
                // use a set to remove duplicate attrIds
                ids = [... new Set(ids)];
                
                if(ids.length>0){
                    // batch call for current_values
                    let aResponse = await fetch(`/index.php?option=com_thinkiq&task=node.getLiveData&ids=${ids.join(',')}&getTimeSeriesData=0`);
                    let aData = await aResponse.json();
                    // console.log(aData);

                    Object.keys(this.subscriptions).forEach(aKey => {
                        // go through all subscriptions
                        let aSubscription = this.subscriptions[aKey];
                        if(aSingleSubscription==null || aKey == aSingleSubscriptionKey){
                            let subscriptions = {};
                            // go through all attrIds the subscription is good for and compile subscriptions object
                            // the subscriptionProps are the keys to the object
                            // vst are the values
                            for(let i=0; i<aSubscription.attrIds.length; i++){
                                if(aSubscription.attrIds[i]){
                                    let aRecord = aData.data.find(x=>x.id==aSubscription.attrIds[i]);
                                    if(aRecord){
                                        subscriptions[aSubscription.subscriptionProps[i]] = {
                                            v: aRecord.current_value,
                                            s: aRecord.current_status,
                                            t: aRecord.current_timestamp
                                        }
                                    }
                                }
                            }
                            // mutating the subscriptions object on the widget will trigger an update of the UI
                            this.layout.find(x=>x.i==aKey).subscriptions = subscriptions;
                        }
                    });
                }
            },
            UpdateSubscriptions: async function(aSubscription){
                // this is a callback from a component that needs to update/register a subscription

                let aSubscriptionKey = Object.keys(aSubscription)[0];
                let aSubscriptionValue = aSubscription[aSubscriptionKey];
                // console.log('update subscription', aSubscription);
                if(!Object.keys(this.subscriptions).includes(aSubscriptionKey)){
                    // if this component doesn't have a key in the subscription object yet, create it
                    this.subscriptions[aSubscriptionKey] = {};
                }

                this.subscriptions[aSubscriptionKey].attrIds = aSubscriptionValue.attrIds;
                this.subscriptions[aSubscriptionKey].subscriptionProps = aSubscriptionValue.subscriptionProps;

                // fill the subscription right away
                await this.FillSubscriptions(aSubscription);

            },
            ToggleViewMode: function(){
                this.dashboardIsEditMode = !this.dashboardIsEditMode;
            },
            AddWidget: function(aWidget){
                let newPanel = new Panel(aWidget.componentName, aWidget.fqn, aWidget.label, {}, {}, 10, 3);
                this.layout.push(newPanel);
            },
            SaveDashboardAsync: async function(){
                await DashboardSdk.SaveDashboardAsync(this.context.std_inputs.node_id, this.layout, this.pageTitle, this.tabTitle);
            },
            SaveConfig: function(e){
                // console.log(e);
                this.layout.find(x=>x.i==e.id).config=e.config;
            },
            removeStepFromGrid: function(val){
                const index = this.layout.map(item => item.i).indexOf(val);
                this.layout.splice(index, 1);
            },
            moveEvent: function(i, newX, newY){
                const msg = "MOVE i=" + i + ", X=" + newX + ", Y=" + newY;
                // this.eventLog.push(msg);
                console.log(msg);
            },
            movedEvent: function(i, newX, newY){
                const msg = "MOVED i=" + i + ", X=" + newX + ", Y=" + newY;
                // this.eventLog.push(msg);
                console.log(msg);
            },
            resizeEvent: function(i, newH, newW, newHPx, newWPx){
                const msg = "RESIZE i=" + i + ", H=" + newH + ", W=" + newW + ", H(px)=" + newHPx + ", W(px)=" + newWPx;
                this.layout.find(x=>x.i==i).wPx = newWPx;
                this.layout.find(x=>x.i==i).hPx = newHPx;
                console.log(msg);
            },
            resizedEvent: function(i, newX, newY, newHPx, newWPx){
                const msg = "RESIZED i=" + i + ", X=" + newX + ", Y=" + newY + ", H(px)=" + newHPx + ", W(px)=" + newWPx;
                this.layout.find(x=>x.i==i).wPx = newWPx;
                this.layout.find(x=>x.i==i).hPx = newHPx;
                console.log(msg);
            },
            containerResizedEvent: function(i, newH, newW, newHPx, newWPx){
                const msg = "CONTAINER RESIZED i=" + i + ", H=" + newH + ", W=" + newW + ", H(px)=" + newHPx + ", W(px)=" + newWPx;
                this.layout.find(x=>x.i==i).wPx = Number(newWPx);
                this.layout.find(x=>x.i==i).hPx = Number(newHPx);
                console.log(msg);
            },
            layoutCreatedEvent: function(newLayout){
                // this.eventLog.push("Created layout");
                console.log("Created layout: ", newLayout)
            },
            layoutBeforeMountEvent: function(newLayout){
                // this.eventLog.push("beforeMount layout");
                console.log("beforeMount layout: ", newLayout)
            },
            layoutMountedEvent: function(newLayout){
                // this.eventLog.push("Mounted layout");
                console.log("Mounted layout: ", newLayout)
            },
            layoutReadyEvent: function(newLayout){
                // this.eventLog.push("Ready layout");
                console.log("Ready layout: ", newLayout)
            },
            layoutUpdatedEvent: function(newLayout){
                // this.eventLog.push("Updated layout");
                console.log("Updated layout: ", newLayout)
            },
        }
    }
     
    var app = createApp(appJson);

    let widgets = <?php echo json_encode($widgetsLight)?>;
    
    widgets.forEach(aWidget => {
        app.component(aWidget.componentName, window[aWidget.componentName]);
    });

    app.mount('#app');
</script>